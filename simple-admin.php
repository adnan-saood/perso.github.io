<?php
session_start();

// Simple password protection
$password = "admin123"; // Change this for production!

if (!isset($_SESSION['authenticated'])) {
    if (isset($_POST['password']) && $_POST['password'] === $password) {
        $_SESSION['authenticated'] = true;
    } else {
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <title>Admin Login</title>
            <style>
                body { font-family: Arial; max-width: 400px; margin: 100px auto; padding: 20px; }
                input { padding: 10px; margin: 5px 0; width: 100%; box-sizing: border-box; }
                button { background: #007cba; color: white; padding: 10px 20px; border: none; cursor: pointer; }
            </style>
        </head>
        <body>
            <h2>Admin Login</h2>
            <form method="post">
                <input type="password" name="password" placeholder="Password" required>
                <button type="submit">Login</button>
            </form>
        </body>
        </html>
        <?php
        exit;
    }
}

// File management functions
function listMarkdownFiles($dir) {
    $files = [];
    if (is_dir($dir)) {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir)
        );
        foreach ($iterator as $file) {
            if ($file->getExtension() === 'md') {
                $files[] = $file->getPathname();
            }
        }
    }
    return $files;
}

function readMarkdownFile($filepath) {
    return file_exists($filepath) ? file_get_contents($filepath) : '';
}

function saveMarkdownFile($filepath, $content) {
    return file_put_contents($filepath, $content);
}

// Handle actions
if (isset($_POST['action'])) {
    switch ($_POST['action']) {
        case 'save':
            saveMarkdownFile($_POST['filepath'], $_POST['content']);
            echo '<div style="background: #d4edda; padding: 10px; margin: 10px 0; border-radius: 5px;">File saved successfully!</div>';
            break;
        case 'logout':
            session_destroy();
            header('Location: ' . $_SERVER['PHP_SELF']);
            exit;
    }
}

// Get file list
$directories = ['_posts', '_projects', '_news', '_teaching', '_pages'];
$files = [];
foreach ($directories as $dir) {
    $files = array_merge($files, listMarkdownFiles($dir));
}

$currentFile = $_GET['file'] ?? ($files[0] ?? '');
$currentContent = $currentFile ? readMarkdownFile($currentFile) : '';
?>

<!DOCTYPE html>
<html>
<head>
    <title>Simple Jekyll Admin</title>
    <style>
        body { font-family: Arial; margin: 0; padding: 20px; }
        .container { display: flex; gap: 20px; }
        .sidebar { width: 300px; border-right: 1px solid #ddd; padding-right: 20px; }
        .editor { flex: 1; }
        .file-list { list-style: none; padding: 0; }
        .file-list li { padding: 5px; margin: 2px 0; }
        .file-list a { text-decoration: none; color: #333; }
        .file-list a:hover { background: #f0f0f0; display: block; }
        .file-list .active { background: #007cba; color: white; }
        textarea { width: 100%; height: 500px; font-family: monospace; }
        button { background: #007cba; color: white; padding: 10px 20px; border: none; cursor: pointer; margin: 5px; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Simple Jekyll Admin</h1>
        <form method="post" style="margin: 0;">
            <input type="hidden" name="action" value="logout">
            <button type="submit">Logout</button>
        </form>
    </div>

    <div class="container">
        <div class="sidebar">
            <h3>Files</h3>
            <ul class="file-list">
                <?php foreach ($files as $file): ?>
                    <li>
                        <a href="?file=<?php echo urlencode($file); ?>" 
                           class="<?php echo $file === $currentFile ? 'active' : ''; ?>">
                            <?php echo basename($file); ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <div class="editor">
            <?php if ($currentFile): ?>
                <form method="post">
                    <h3>Editing: <?php echo $currentFile; ?></h3>
                    <input type="hidden" name="action" value="save">
                    <input type="hidden" name="filepath" value="<?php echo $currentFile; ?>">
                    <textarea name="content"><?php echo htmlspecialchars($currentContent); ?></textarea>
                    <br>
                    <button type="submit">Save File</button>
                </form>
            <?php else: ?>
                <p>Select a file to edit</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>