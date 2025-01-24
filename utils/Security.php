<?php
class Security {
    private static $key = 'event-management-key';
    private static $cipher = 'AES-256-CBC';

    public static function encrypt($data) {
        $ivlen = openssl_cipher_iv_length(self::$cipher);
        $iv = openssl_random_pseudo_bytes($ivlen);
        $encrypted = openssl_encrypt(
            (string)$data, 
            self::$cipher, 
            self::$key, 
            0, 
            $iv
        );
        return base64_encode($iv . $encrypted);
    }

    public static function decrypt($data) {
        try {
            $data = base64_decode($data);
            if ($data === false) {
                return false;
            }

            $ivlen = openssl_cipher_iv_length(self::$cipher);
            $iv = substr($data, 0, $ivlen);
            $encrypted = substr($data, $ivlen);

            $decrypted = openssl_decrypt(
                $encrypted, 
                self::$cipher, 
                self::$key, 
                0, 
                $iv
            );

            return $decrypted !== false ? (int)$decrypted : false;
        } catch (Exception $e) {
            error_log('Decryption error: ' . $e->getMessage());
            return false;
        }
    }
}