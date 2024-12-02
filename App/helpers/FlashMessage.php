<?php

namespace helpers;

class FlashMessage {

    public static function setMessage($message, $type = 'success') {
        $_SESSION['flash_message'] = [
            'message' => $message,
            'type' => $type 
        ];
    }

    public static function getMessage() {
        if (isset($_SESSION['flash_message'])) {
            $message = $_SESSION['flash_message'];
            unset($_SESSION['flash_message']);
            return $message;
        }
        return null;
    }
}


