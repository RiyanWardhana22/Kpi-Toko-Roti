<?php
function base_url($path = '')
{
            $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
            $host = $_SERVER['HTTP_HOST'];
            $script_name = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
            $project_root = rtrim($script_name, '/');
            if (basename($project_root) == 'core') {
                        $project_root = dirname($project_root);
            }

            $url = "$protocol://$host" . rtrim($project_root, '/') . '/' . ltrim($path, '/');
            return $url;
}

function redirect($url)
{
            header("Location: " . $url);
            exit();
}
