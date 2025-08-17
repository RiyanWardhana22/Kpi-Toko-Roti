<!DOCTYPE html>
<html lang="en">

<head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Password Hashing Tool</title>
            <style>
                        body {
                                    font-family: Arial, sans-serif;
                                    max-width: 600px;
                                    margin: 0 auto;
                                    padding: 20px;
                                    line-height: 1.6;
                        }

                        h1 {
                                    color: #333;
                                    text-align: center;
                        }

                        .form-group {
                                    margin-bottom: 15px;
                        }

                        label {
                                    display: block;
                                    margin-bottom: 5px;
                                    font-weight: bold;
                        }

                        input[type="password"] {
                                    width: 100%;
                                    padding: 8px;
                                    border: 1px solid #ddd;
                                    border-radius: 4px;
                                    box-sizing: border-box;
                        }

                        button {
                                    background-color: #4CAF50;
                                    color: white;
                                    padding: 10px 15px;
                                    border: none;
                                    border-radius: 4px;
                                    cursor: pointer;
                                    font-size: 16px;
                        }

                        button:hover {
                                    background-color: #45a049;
                        }

                        .result {
                                    margin-top: 20px;
                                    padding: 15px;
                                    background-color: #f5f5f5;
                                    border-radius: 4px;
                                    word-wrap: break-word;
                        }

                        .hash-type {
                                    margin-top: 10px;
                                    font-size: 14px;
                                    color: #666;
                        }
            </style>
</head>

<body>
            <h1>Password Hashing Tool</h1>

            <form method="post">
                        <div class="form-group">
                                    <label for="password">Masukkan Password:</label>
                                    <input type="password" id="password" name="password" required>
                        </div>

                        <button type="submit" name="hash">Hash Password</button>
            </form>

            <?php
            if (isset($_POST['hash']) && !empty($_POST['password'])) {
                        $password = $_POST['password'];

                        // Hash password menggunakan berbagai algoritma
                        $md5 = md5($password);
                        $sha1 = sha1($password);
                        $bcrypt = password_hash($password, PASSWORD_BCRYPT);
                        $argon2i = password_hash($password, PASSWORD_ARGON2I);

                        echo '<div class="result">';
                        echo '<h3>Hasil Hashing:</h3>';

                        echo '<div class="hash-type"><strong>MD5:</strong></div>';
                        echo '<div>' . htmlspecialchars($md5) . '</div>';

                        echo '<div class="hash-type"><strong>SHA1:</strong></div>';
                        echo '<div>' . htmlspecialchars($sha1) . '</div>';

                        echo '<div class="hash-type"><strong>BCRYPT:</strong></div>';
                        echo '<div>' . htmlspecialchars($bcrypt) . '</div>';

                        if ($argon2i) {
                                    echo '<div class="hash-type"><strong>ARGON2I:</strong></div>';
                                    echo '<div>' . htmlspecialchars($argon2i) . '</div>';
                        }

                        echo '</div>';
            }
            ?>

            <div style="margin-top: 30px; font-size: 14px; color: #666;">
                        <p><strong>Catatan:</strong></p>
                        <ul>
                                    <li>MD5 dan SHA1 tidak aman untuk penyimpanan password di sistem produksi</li>
                                    <li>Gunakan BCRYPT atau ARGON2I untuk keamanan yang lebih baik</li>
                                    <li>Jangan pernah menyimpan password plaintext di database</li>
                        </ul>
            </div>
</body>

</html>