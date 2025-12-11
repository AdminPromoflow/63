<?php
// Include PHPMailer and its dependencies
require '../assets/lib/send-email/PHPMailer/src/Exception.php';
require '../assets/lib/send-email/PHPMailer/src/PHPMailer.php';
require '../assets/lib/send-email/PHPMailer/src/SMTP.php';

// Import PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class EmailSender {
    private $message;
    private $recipientEmail;
    private $recipientName;
    private $recipientPassword;
    private $recipientTableOrder;

    // Setter for recipient email
    public function setRecipientEmail($recipientEmail) {
        $this->recipientEmail = $recipientEmail;

    }

    // Setter for recipient name
    public function setRecipientName($recipientName) {
        $this->recipientName = $recipientName;
    }

    // Setter for recipient password (optional)
    public function setRecipientPassword($recipientPassword) {
        $this->recipientPassword = $recipientPassword;
    }

    // Method to send a registration email
    public function sendEmailRegistration() {
      try {
        $mail = new PHPMailer(true);

        // ===== SMTP (Hostinger) =====
        $mail->isSMTP();
        $mail->SMTPDebug = 0;
        $mail->Host       = 'smtp.hostinger.com';
        $mail->Port       = 587; // STARTTLS
        $mail->SMTPAuth   = true;
        $mail->Username   = 'admin@lanyardsforyou.com';
        $mail->Password   = '32skiff32!CI';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;

        $mail->CharSet    = 'UTF-8';
        $mail->Encoding   = 'base64';

        // ===== Remitente y destinatarios =====
        $mail->setFrom('admin@lanyardsforyou.com', 'Ian Southworth');
        $mail->addReplyTo('admin@lanyardsforyou.com', 'Ian Southworth');
        $mail->addAddress($this->recipientEmail, $this->recipientName);

        // ===== Asunto =====
        $mail->Subject = 'Welcome to Lanyards For You';

        // ===== Datos con escape seguro =====
        $name     = htmlspecialchars((string)$this->recipientName, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $email    = htmlspecialchars((string)$this->recipientEmail, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $password = htmlspecialchars((string)($this->recipientPassword ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $year     = date('Y');

        // ===== Cuerpo HTML (todo inline, blanco/negro, en inglés británico) =====
        $mail->isHTML(true);
        $mail->Body = <<<HTML
    <!doctype html>
    <html lang="en-GB">
      <body style="margin:0; padding:0; background:#ffffff;">
        <!-- Preheader (hidden) -->
        <div style="display:none; max-height:0; overflow:hidden; line-height:1px; color:#ffffff; opacity:0;">
          Your sign-in details are inside. Please change your password straightaway after your first sign-in.
        </div>

        <div style="width:100%; background:#ffffff;">
          <div style="max-width:640px; margin:0 auto; padding:24px; border:1px solid #000000; box-sizing:border-box;">
            <div style="margin:0 0 8px 0;">
              <p style="margin:0; font-family:Arial, Helvetica, sans-serif; font-size:12px; line-height:1.4; color:#000000;">
                .63
              </p>
            </div>

            <div style="margin:0 0 16px 0;">
              <h1 style="margin:0; font-family:Arial, Helvetica, sans-serif; font-size:22px; line-height:1.3; color:#000000;">
                Welcome aboard
              </h1>
            </div>

            <div style="margin:0 0 12px 0;">
              <p style="margin:0; font-family:Arial, Helvetica, sans-serif; font-size:14px; line-height:1.6; color:#000000;">
                Hello {$name},
              </p>
            </div>

            <div style="margin:0 0 16px 0;">
              <p style="margin:0; font-family:Arial, Helvetica, sans-serif; font-size:14px; line-height:1.6; color:#000000;">
                We’re delighted to have you with us at .63. Below are your sign-in details. Please keep them safe.
              </p>
            </div>

            <div style="margin:16px 0; padding:12px 0; border-top:1px solid #000000; border-bottom:1px solid #000000;">
              <p style="margin:0 0 8px 0; font-family:Arial, Helvetica, sans-serif; font-size:14px; color:#000000;">
                <strong style="display:inline-block; width:120px;">Email:</strong>
                <span style="font-family:Arial, Helvetica, sans-serif; color:#000000;">{$email}</span>
              </p>
              <p style="margin:0; font-family:Arial, Helvetica, sans-serif; font-size:14px; color:#000000;">
                <strong style="display:inline-block; width:120px;">Password:</strong>
                <span style="font-family:Arial, Helvetica, sans-serif; color:#000000;">{$password}</span>
              </p>
            </div>


            <div style="margin:0 0 8px 0;">
              <p style="margin:0; font-family:Arial, Helvetica, sans-serif; font-size:13px; line-height:1.6; color:#000000;">
                If you didn’t request this account, please let us know immediately by replying to this email.
              </p>
            </div>

            <div style="margin:16px 0 0 0;">
              <p style="margin:0; font-family:Arial, Helvetica, sans-serif; font-size:13px; color:#000000;">
                Kind regards,<br>
                .63 For You Team
              </p>
            </div>
          </div>

          <div style="max-width:640px; margin:8px auto 0 auto; text-align:center;">
            <p style="margin:0; font-family:Arial, Helvetica, sans-serif; font-size:11px; color:#000000;">
              © {$year} Lanyards For You. All rights reserved.
            </p>
          </div>
        </div>
      </body>
    </html>
    HTML;

        // ===== Texto plano (fallback) =====
        $mail->AltBody =
          "Hello {$name},\n\n" .
          "Welcome to .63. Here are your sign-in details:\n" .
          "Email: {$email}\n" .
          "Password: {$password}\n\n" .
          "For your security, please change this password straightaway after your first sign-in.\n" .
          "Sign in: https://lanyardsforyou.com/login\n\n" .
          "If you didn’t request this account, please let us know immediately by replying to this email.\n\n" .
          "Kind regards,\nLanyards For You Team\n© {$year} Lanyards For You";

        // ===== Enviar =====
        return $mail->send();
      } catch (Exception $e) {
        error_log('EmailSender::sendEmailRegistration error -> ' . $e->getMessage());
        return false;
      }
    }

}
