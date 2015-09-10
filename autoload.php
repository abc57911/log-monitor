<?php

spl_autoload_register(function ($full_classname) {
    $filename = __DIR__ . '/' . str_replace('\\', '/', $full_classname . '.php');
    $filename_lower = __DIR__ . '/' . str_replace('\\', '/', strtolower($full_classname) . '.php');
    if (file_exists($filename)) {
        include $filename;
    } else if (file_exists($filename_lower)) {
        include $filename_lower;
    }
});
