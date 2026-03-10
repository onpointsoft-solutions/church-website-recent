<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php'; // Path to autoload.php

// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data and sanitize
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $subject = filter_input(INPUT_POST, 'subject', FILTER_SANITIZE_STRING);
    $message = filter_input(INPUT_POST, 'message', FILTER_SANITIZE_STRING);
    
    // Server-side validation
    $errors = [];
    
    if (empty($name)) {
        $errors[] = "Name is required";
    }
    
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Valid email is required";
    }
    
    if (empty($message)) {
        $errors[] = "Message is required";
    }
    
    // If no validation errors, proceed with sending email
    if (empty($errors)) {
        $mail = new PHPMailer(true);
        $mailcfg = require '../phpmailer_config.php';
        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host = $mailcfg['host']; // Update with your SMTP server
            $mail->SMTPAuth = true;
            $mail->Username = $mailcfg['username']; // Update with your email
            $mail->Password = $mailcfg['password']; // Use App Password for Gmail
            $mail->SMTPSecure = $mailcfg['secure'];
            $mail->Port = $mailcfg['port'];
            
            // Recipients
            $mail->setFrom($email, $name);
            $mail->addAddress('idkituyi@gmail.com', 'Christ Ekklesia Fellowship');
            $mail->addReplyTo($email, $name);
            
            // Content
            $mail->isHTML(true);
            $mail->Subject = "Contact Form: " . (!empty($subject) ? $subject : 'No Subject');
            $mail->Body    = "
                <h2>New Contact Form Submission</h2>
                <p><strong>Name:</strong> {$name}</p>
                <p><strong>Email:</strong> {$email}</p>
                <p><strong>Subject:</strong> " . (!empty($subject) ? $subject : 'Not specified') . "</p>
                <p><strong>Message:</strong></p>
                <p>" . nl2br(htmlspecialchars($message)) . "</p>
            ";
            
            $mail->AltBody = "
                New Contact Form Submission\n\n" .
                "Name: {$name}\n" .
                "Email: {$email}\n" .
                "Subject: " . (!empty($subject) ? $subject : 'Not specified') . "\n\n" .
                "Message:\n" . $message;
            
            $mail->send();
            
            // Success message
            $response = [
                'status' => 'success',
                'message' => 'Your message has been sent. Thank you!'
            ];
            
        } catch (Exception $e) {
            $response = [
                'status' => 'error',
                'message' => 'Message could not be sent. Mailer Error: ' . $mail->ErrorInfo
            ];
        }
    } else {
        // Validation errors
        $response = [
            'status' => 'error',
            'message' => 'Please fix the following errors:',
            'errors' => $errors
        ];
    }
    
    // Return JSON response
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

// If not a POST request, redirect to home
header('Location: /');
exit;
