<?php
// Contact-form inbox: one JSON file per message, optional email notification.

if (!defined('CMS_BOOT')) { http_response_code(404); exit; }

function cms_messages_dir()
{
    return cms_data_path('messages');
}

function cms_clean_line($s, $max)
{
    $s = trim(preg_replace('/[\r\n\t]+/', ' ', (string) $s));
    return function_exists('mb_substr') ? mb_substr($s, 0, $max) : substr($s, 0, $max);
}

// Validates and stores a submission. Returns array(ok, error-message).
function cms_receive_message(array $in)
{
    // Bots fill the hidden "website" field and submit instantly.
    if (!empty($in['website'])) return array(true, null);
    $started = isset($in['t']) ? (int) $in['t'] : 0;
    if ($started > 0 && time() - (int) ($started / 1000) < 3) return array(false, 'That was fast! Please wait a few seconds and send again.');

    $name = cms_clean_line(isset($in['name']) ? $in['name'] : '', 120);
    $email = cms_clean_line(isset($in['email']) ? $in['email'] : '', 200);
    $subject = cms_clean_line(isset($in['subject']) ? $in['subject'] : '', 200);
    $body = trim(str_replace("\r\n", "\n", (string) (isset($in['message']) ? $in['message'] : '')));

    if ($name === '' || $body === '') return array(false, 'Please fill in your name and a message.');
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) return array(false, 'Please enter a valid email address.');
    if (strlen($body) > 10000) return array(false, 'Message is too long (10,000 characters max).');
    if (!cms_throttle_hit('contact', 3600, 5)) return array(false, 'Too many messages from your connection. Please try again later.');

    $id = date('Ymd-His') . '-' . bin2hex(random_bytes(4));
    $msg = array(
        'id' => $id,
        'received' => time(),
        'name' => $name,
        'email' => $email,
        'subject' => $subject,
        'message' => $body,
        'read' => false,
        'ip_hash' => substr(hash('sha256', cms_client_ip() . cms_config('base_url')), 0, 16),
        'user_agent' => cms_clean_line(isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '', 200),
    );
    if (!cms_write_json(cms_messages_dir() . '/' . $id . '.json', $msg)) {
        return array(false, 'Sorry, the message could not be saved. Please email me directly.');
    }
    cms_notify_new_message($msg);
    return array(true, null);
}

function cms_notify_new_message(array $msg)
{
    $to = (string) cms_settings('notify_email');
    if ($to === '' || !function_exists('mail')) return false;
    $subject = 'Website message: ' . ($msg['subject'] !== '' ? $msg['subject'] : 'from ' . $msg['name']);
    $link = (cms_is_https() ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . cms_url('admin/?page=message&id=' . $msg['id']);
    $text = "From: {$msg['name']} <{$msg['email']}>\n"
        . 'Date: ' . date('D j M Y, H:i', $msg['received']) . "\n\n"
        . $msg['message'] . "\n\n--\nReply directly to this email, or open it in the admin panel:\n" . $link . "\n";
    return cms_send_mail($to, $subject, $text, $msg['email'], $msg['name']);
}

function cms_send_mail($to, $subject, $text, $replyTo = '', $replyName = '')
{
    $from = (string) cms_settings('from_email');
    $headers = array(
        'MIME-Version: 1.0',
        'Content-Type: text/plain; charset=UTF-8',
        'Content-Transfer-Encoding: 8bit',
    );
    if ($from !== '' && filter_var($from, FILTER_VALIDATE_EMAIL)) $headers[] = 'From: ' . cms_config('site_name') . ' website <' . $from . '>';
    if ($replyTo !== '' && filter_var($replyTo, FILTER_VALIDATE_EMAIL)) {
        // Name is already stripped of newlines; quote it to keep the header valid.
        $headers[] = 'Reply-To: "' . str_replace('"', '', $replyName) . '" <' . $replyTo . '>';
    }
    $encSubject = '=?UTF-8?B?' . base64_encode(cms_clean_line($subject, 200)) . '?=';
    $params = ($from !== '' && filter_var($from, FILTER_VALIDATE_EMAIL)) ? '-f' . $from : '';
    return @mail($to, $encSubject, $text, implode("\r\n", $headers), $params);
}

function cms_list_messages()
{
    $out = array();
    foreach (glob(cms_messages_dir() . '/*.json') ?: array() as $f) {
        $m = cms_read_json($f, null);
        if ($m) $out[] = $m;
    }
    usort($out, function ($a, $b) { return $b['received'] - $a['received']; });
    return $out;
}

function cms_valid_message_id($id)
{
    return is_string($id) && preg_match('/^\d{8}-\d{6}-[a-f0-9]{8}$/', $id) === 1;
}

function cms_load_message($id)
{
    if (!cms_valid_message_id($id)) return null;
    return cms_read_json(cms_messages_dir() . '/' . $id . '.json', null);
}

function cms_set_message_read($id, $read)
{
    $m = cms_load_message($id);
    if (!$m) return false;
    $m['read'] = (bool) $read;
    return cms_write_json(cms_messages_dir() . '/' . $id . '.json', $m);
}

function cms_delete_message($id)
{
    if (!cms_valid_message_id($id)) return false;
    $f = cms_messages_dir() . '/' . $id . '.json';
    return is_file($f) && cms_move_to_trash($f, 'message-' . $id . '.json', array('kind' => 'message', 'path' => 'messages/' . $id . '.json'));
}

function cms_unread_count()
{
    $n = 0;
    foreach (cms_list_messages() as $m) if (empty($m['read'])) $n++;
    return $n;
}
