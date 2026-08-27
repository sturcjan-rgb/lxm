<?php
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // 1. Zadejte cílový e-mail, kam mají registrace chodit
    $to = "vas-email@lexum.sk"; // <-- ZMĚŇTE NA VÁŠ E-MAIL
    $subject = "Nová registrácia: Premium Cataract Academy 2026";

    // 2. Sanitizace a validace vstupů
    $first_name = trim(filter_input(INPUT_POST, 'first_name', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    $last_name  = trim(filter_input(INPUT_POST, 'last_name', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    $workplace  = trim(filter_input(INPUT_POST, 'workplace', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    $email      = trim(filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL));
    $phone      = trim(filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    $attendance = trim(filter_input(INPUT_POST, 'attendance', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    $note       = trim(filter_input(INPUT_POST, 'note', FILTER_SANITIZE_FULL_SPECIAL_CHARS));

    // Kontrola povinných polí
    if (!$first_name || !$last_name || !$workplace || !$email || !$phone || !$attendance) {
        header("Location: index.php?status=error#registracia");
        exit;
    }

    // 3. Sestavení těla e-mailu
    $message = "Bola prijatá nová registrácia na sympózium:\n\n";
    $message .= "Meno a titul: " . $first_name . "\n";
    $message .= "Priezvisko: " . $last_name . "\n";
    $message .= "Pracovisko / Klinika: " . $workplace . "\n";
    $message .= "E-mail: " . $email . "\n";
    $message .= "Telefón: " . $phone . "\n";
    $message .= "Rozsah účasti: " . $attendance . "\n";
    $message .= "Poznámka / diéta: " . ($note ? $note : "Žiadna") . "\n";
    $message .= "Čas odoslania: " . date("Y-m-d H:i:s") . "\n";

    // Hlavičky e-mailu
    $headers = "From: noreply@" . $_SERVER['HTTP_HOST'] . "\r\n";
    $headers .= "Reply-To: " . $email . "\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion();

    // 4. Odeslání e-mailu
    $mail_sent = mail($to, '=?UTF-8?B?'.base64_encode($subject).'?=', $message, $headers);

    // 5. Uložení zálohy do CSV souboru (registrations.csv)
    $csv_file = __DIR__ . '/registrations.csv';
    $is_new = !file_exists($csv_file);
    $csv_handle = fopen($csv_file, 'a');
    if ($csv_handle) {
        if ($is_new) {
            // Hlavička CSV souboru
            fputcsv($csv_handle, ['Dátum a čas', 'Meno', 'Priezvisko', 'Pracovisko', 'E-mail', 'Telefón', 'Účasť', 'Poznámka'], ';');
        }
        fputcsv($csv_handle, [date('Y-m-d H:i:s'), $first_name, $last_name, $workplace, $email, $phone, $attendance, $note], ';');
        fclose($csv_handle);
    }

    // 6. Přesměrování zpět s hláškou o úspěchu
    header("Location: index.php?status=success#registracia");
    exit;
} else {
    // Přímý přístup zakázán
    header("Location: index.php");
    exit;
}
?>