<?php

set_error_handler(function($severity, $message, $file, $line) {
    throw new ErrorException($message, 0, $severity, $file, $line);
});

spl_autoload_register(function($class) {
    trigger_error("$class not loaded.", E_USER_ERROR);
});
