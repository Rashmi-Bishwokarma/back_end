<?php
header("Access-Control-Allow-Origin: *");

// Allow the following methods from any origin
header("Access-Control-Allow-Methods: POST");

// Allow the following headers from any origin
header("Access-Control-Allow-Headers: Content-Type");
function encryptJournalEntry($data, $encryptionKey) {
    $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
    $encryptedData = openssl_encrypt($data, 'aes-256-cbc', $encryptionKey, 0, $iv);
    return base64_encode($iv . '::' . $encryptedData);
}

function decryptJournalEntry($data, $encryptionKey) {
    list($iv, $encryptedData) = explode('::', base64_decode($data), 2);
    return openssl_decrypt($encryptedData, 'aes-256-cbc', $encryptionKey, 0, $iv);
}

?>
