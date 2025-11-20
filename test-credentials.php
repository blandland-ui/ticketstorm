<?php
// Test different credential combinations
header('Content-Type: text/html; charset=utf-8');

echo "<h2>Database Credential Test</h2>";

// Test credentials from hosting panel
$tests = [
    [
        'host' => 'localhost',
        'user' => 'cpses_blwvbujfkq',
        'pass' => 'The  hater#',
        'db' => 'blacoksf_blacksitedb_database',
        'label' => 'Current config (two spaces)'
    ],
    [
        'host' => 'localhost',
        'user' => 'cpses_blwvbujfkq',
        'pass' => 'The hater#',
        'db' => 'blacoksf_blacksitedb_database',
        'label' => 'One space version'
    ],
    [
        'host' => 'localhost',
        'user' => 'blacoksf_admin',
        'pass' => 'The  hater#',
        'db' => 'blacoksf_blacksitedb_database',
        'label' => 'blacoksf_admin user'
    ],
];

foreach ($tests as $test) {
    echo "<h3>Testing: {$test['label']}</h3>";
    echo "<pre>";
    echo "Host: {$test['host']}\n";
    echo "User: {$test['user']}\n";
    echo "Pass: " . str_repeat('*', strlen($test['pass'])) . " (length: " . strlen($test['pass']) . ")\n";
    echo "DB: {$test['db']}\n";
    
    $conn = @new mysqli($test['host'], $test['user'], $test['pass'], $test['db']);
    
    if ($conn->connect_error) {
        echo "<span style='color:red'>❌ FAILED: {$conn->connect_error}</span>\n";
    } else {
        echo "<span style='color:green'>✅ SUCCESS!</span>\n";
        echo "Server version: {$conn->server_info}\n";
        $conn->close();
    }
    echo "</pre><hr>";
}

echo "<h3>Password Character Analysis</h3>";
echo "<pre>";
$pass = 'The  hater#';
echo "Length: " . strlen($pass) . " characters\n";
echo "Characters: ";
for ($i = 0; $i < strlen($pass); $i++) {
    $char = $pass[$i];
    $ord = ord($char);
    echo "[$char:$ord] ";
}
echo "\n</pre>";

echo "<p><strong>Check your hosting panel (cPanel/DirectAdmin) for the exact database credentials.</strong></p>";
echo "<p>Look for: Database User, Database Name, and Database Password</p>";
?>
