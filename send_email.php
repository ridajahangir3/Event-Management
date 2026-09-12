<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

$mail = new PHPMailer(true);

try {
    // SMTP settings
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'ridajahangir187@gmail.com';
    $mail->Password   = 'ewry nniu stsr mnqe';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;

    // Sender
    $mail->setFrom('ridajahangir187@gmail.com', 'College Event Hub');

    // Receiver
    $mail->addAddress('ridajahangir3@gmail.com');

    // Email content
    $mail->isHTML(true);
    $mail->Subject = 'Event Notification';
    $mail->Body    = 'A new college event has been added.';

    $mail->send();

    echo "Email sent successfully!";
} catch (Exception $e) {
    echo "Email could not be sent. Error: {$mail->ErrorInfo}";
}
?>