<?php
function verify_recaptcha(): bool {
    $recaptcha_response = $_POST['g-recaptcha-response'] ?? '';
    if (empty($recaptcha_response)) {
        return false;
    }
    $secret = $_ENV['RECAPTCHA_SECRET_KEY'] ?? '';
    if (empty($secret)) {
        return false;
    }
    $ctx = stream_context_create(['http' => ['timeout' => 5]]);
    $verify_response = @file_get_contents(
        "https://www.google.com/recaptcha/api/siteverify?secret={$secret}&response=" . urlencode($recaptcha_response),
        false,
        $ctx
    );
    if (!$verify_response) {
        return false;
    }
    $data = json_decode($verify_response, true);
    return !empty($data['success']) && ($data['score'] ?? 0) >= 0.5;
}
