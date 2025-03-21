<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Mailer {
    private static $instance = null;
    private $config;
    private $mailer;
    private $logger;

    private function __construct() {
        $this->config = require __DIR__ . '/../config/config.php';
        $this->logger = Logger::getInstance();
        $this->setupMailer();
    }

    /**
     * Get Mailer instance (Singleton)
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Setup PHPMailer with configuration
     */
    private function setupMailer() {
        $this->mailer = new PHPMailer(true);

        try {
            // Server settings
            $this->mailer->isSMTP();
            $this->mailer->Host = $this->config['mail_host'];
            $this->mailer->SMTPAuth = true;
            $this->mailer->Username = $this->config['mail_username'];
            $this->mailer->Password = $this->config['mail_password'];
            $this->mailer->SMTPSecure = $this->config['mail_encryption'];
            $this->mailer->Port = $this->config['mail_port'];

            // Default settings
            $this->mailer->setFrom(
                $this->config['mail_from_address'],
                $this->config['mail_from_name']
            );
            $this->mailer->isHTML(true);
            $this->mailer->CharSet = 'UTF-8';

        } catch (Exception $e) {
            $this->logger->error('Mailer setup failed', ['error' => $e->getMessage()]);
            throw new Exception('Failed to setup mailer');
        }
    }

    /**
     * Send email
     */
    public function send($to, $subject, $body, $attachments = [], $cc = [], $bcc = []) {
        try {
            $this->mailer->clearAddresses();
            $this->mailer->clearAttachments();
            $this->mailer->clearCCs();
            $this->mailer->clearBCCs();

            // Add recipients
            if (is_array($to)) {
                foreach ($to as $address) {
                    $this->mailer->addAddress($address);
                }
            } else {
                $this->mailer->addAddress($to);
            }

            // Add CC recipients
            foreach ($cc as $address) {
                $this->mailer->addCC($address);
            }

            // Add BCC recipients
            foreach ($bcc as $address) {
                $this->mailer->addBCC($address);
            }

            // Add attachments
            foreach ($attachments as $attachment) {
                if (is_array($attachment)) {
                    $this->mailer->addAttachment(
                        $attachment['path'],
                        $attachment['name'] ?? basename($attachment['path'])
                    );
                } else {
                    $this->mailer->addAttachment($attachment);
                }
            }

            $this->mailer->Subject = $subject;
            $this->mailer->Body = $this->renderTemplate($body);
            $this->mailer->AltBody = strip_tags($body);

            $result = $this->mailer->send();
            
            $this->logger->info('Email sent successfully', [
                'to' => $to,
                'subject' => $subject
            ]);

            return $result;

        } catch (Exception $e) {
            $this->logger->error('Failed to send email', [
                'error' => $e->getMessage(),
                'to' => $to,
                'subject' => $subject
            ]);
            throw new Exception('Failed to send email: ' . $e->getMessage());
        }
    }

    /**
     * Send template email
     */
    public function sendTemplate($to, $template, $data = [], $attachments = [], $cc = [], $bcc = []) {
        $templatePath = __DIR__ . '/../templates/emails/' . $template . '.php';
        
        if (!file_exists($templatePath)) {
            throw new Exception('Email template not found: ' . $template);
        }

        ob_start();
        extract($data);
        include $templatePath;
        $body = ob_get_clean();

        return $this->send($to, $data['subject'] ?? '', $body, $attachments, $cc, $bcc);
    }

    /**
     * Render email template with layout
     */
    private function renderTemplate($content) {
        $layoutPath = __DIR__ . '/../templates/emails/layout.php';
        
        if (file_exists($layoutPath)) {
            ob_start();
            include $layoutPath;
            return ob_get_clean();
        }

        return $content;
    }

    /**
     * Send welcome email
     */
    public function sendWelcome($user) {
        return $this->sendTemplate('welcome', [
            'subject' => 'Welcome to ' . $this->config['app_name'],
            'user' => $user
        ]);
    }

    /**
     * Send password reset email
     */
    public function sendPasswordReset($user, $token) {
        $resetUrl = $this->config['app_url'] . '/reset-password?token=' . $token;
        
        return $this->sendTemplate('password-reset', [
            'subject' => 'Reset Your Password',
            'user' => $user,
            'resetUrl' => $resetUrl
        ]);
    }

    /**
     * Send event registration confirmation
     */
    public function sendEventConfirmation($user, $event) {
        return $this->sendTemplate('event-confirmation', [
            'subject' => 'Event Registration Confirmation',
            'user' => $user,
            'event' => $event
        ]);
    }

    /**
     * Send donation receipt
     */
    public function sendDonationReceipt($donation) {
        return $this->sendTemplate('donation-receipt', [
            'subject' => 'Thank You for Your Donation',
            'donation' => $donation
        ]);
    }

    /**
     * Send grant application confirmation
     */
    public function sendGrantConfirmation($user, $grant) {
        return $this->sendTemplate('grant-confirmation', [
            'subject' => 'Grant Application Received',
            'user' => $user,
            'grant' => $grant
        ]);
    }

    /**
     * Prevent cloning of the instance (Singleton)
     */
    private function __clone() {}

    /**
     * Prevent unserializing of the instance (Singleton)
     */
    private function __wakeup() {}
}