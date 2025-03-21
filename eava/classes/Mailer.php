<?php
require_once __DIR__ . '/../config/config.php';

class Mailer {
    private static $instance = null;
    private $mailer;
    private $logger;
    private $from;
    private $fromName;
    private $replyTo;
    private $templates = [];

    private function __construct() {
        $this->logger = Logger::getInstance();
        
        // Set default sender details from config
        $this->from = MAIL_FROM;
        $this->fromName = MAIL_FROM_NAME;
        $this->replyTo = MAIL_REPLY_TO;

        // Load email templates
        $this->loadTemplates();
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Load email templates
     */
    private function loadTemplates() {
        $templateDir = __DIR__ . '/../templates/emails/';
        if (!is_dir($templateDir)) {
            return;
        }

        foreach (glob($templateDir . '*.html') as $file) {
            $name = basename($file, '.html');
            $this->templates[$name] = file_get_contents($file);
        }
    }

    /**
     * Send an email
     */
    public function send($to, $subject, $body, $attachments = [], $options = []) {
        try {
            // Set headers
            $headers = [
                'MIME-Version: 1.0',
                'Content-type: text/html; charset=UTF-8',
                'From: ' . $this->fromName . ' <' . $this->from . '>',
                'Reply-To: ' . ($options['reply_to'] ?? $this->replyTo),
                'X-Mailer: PHP/' . phpversion()
            ];

            // Add CC if provided
            if (!empty($options['cc'])) {
                $headers[] = 'Cc: ' . $options['cc'];
            }

            // Add BCC if provided
            if (!empty($options['bcc'])) {
                $headers[] = 'Bcc: ' . $options['bcc'];
            }

            // Handle attachments
            if (!empty($attachments)) {
                $boundary = md5(time());
                $headers[1] = 'Content-Type: multipart/mixed; boundary="' . $boundary . '"';
                
                $message = "--{$boundary}\r\n";
                $message .= "Content-Type: text/html; charset=UTF-8\r\n";
                $message .= "Content-Transfer-Encoding: base64\r\n\r\n";
                $message .= chunk_split(base64_encode($body));

                foreach ($attachments as $attachment) {
                    if (file_exists($attachment['path'])) {
                        $content = file_get_contents($attachment['path']);
                        $message .= "\r\n--{$boundary}\r\n";
                        $message .= "Content-Type: " . $attachment['type'] . "; name=\"" . $attachment['name'] . "\"\r\n";
                        $message .= "Content-Disposition: attachment; filename=\"" . $attachment['name'] . "\"\r\n";
                        $message .= "Content-Transfer-Encoding: base64\r\n\r\n";
                        $message .= chunk_split(base64_encode($content));
                    }
                }
                
                $message .= "\r\n--{$boundary}--";
            } else {
                $message = $body;
            }

            // Send email
            $success = mail($to, $subject, $message, implode("\r\n", $headers));

            if ($success) {
                $this->logger->info("Email sent successfully to {to}", ['to' => $to]);
                return true;
            } else {
                throw new Exception("Failed to send email");
            }
        } catch (Exception $e) {
            $this->logger->error("Email sending failed: {message}", ['message' => $e->getMessage()]);
            throw new Exception("Failed to send email: " . $e->getMessage());
        }
    }

    /**
     * Send email using template
     */
    public function sendTemplate($to, $template, $data, $attachments = [], $options = []) {
        if (!isset($this->templates[$template])) {
            throw new Exception("Email template not found: " . $template);
        }

        $subject = $data['subject'] ?? '';
        $body = $this->renderTemplate($template, $data);

        return $this->send($to, $subject, $body, $attachments, $options);
    }

    /**
     * Render email template
     */
    private function renderTemplate($template, $data) {
        $content = $this->templates[$template];
        
        // Replace placeholders
        foreach ($data as $key => $value) {
            $content = str_replace('{{' . $key . '}}', $value, $content);
        }

        return $content;
    }

    /**
     * Send welcome email
     */
    public function sendWelcomeEmail($user) {
        $data = [
            'subject' => 'Welcome to ' . SITE_NAME,
            'name' => $user['full_name'],
            'username' => $user['username'],
            'login_url' => SITE_URL . '/login'
        ];

        return $this->sendTemplate($user['email'], 'welcome', $data);
    }

    /**
     * Send password reset email
     */
    public function sendPasswordResetEmail($user, $token) {
        $resetUrl = SITE_URL . '/reset-password?token=' . $token;
        
        $data = [
            'subject' => 'Password Reset Request',
            'name' => $user['full_name'],
            'reset_url' => $resetUrl,
            'expiry_time' => '24 hours'
        ];

        return $this->sendTemplate($user['email'], 'password_reset', $data);
    }

    /**
     * Send donation receipt
     */
    public function sendDonationReceipt($donation, $donor) {
        $data = [
            'subject' => 'Thank You for Your Donation',
            'name' => $donor['name'],
            'amount' => Utility::formatCurrency($donation['amount'], $donation['currency']),
            'date' => date('F j, Y', strtotime($donation['created_at'])),
            'transaction_id' => $donation['transaction_id']
        ];

        return $this->sendTemplate($donor['email'], 'donation_receipt', $data);
    }

    /**
     * Send campaign update
     */
    public function sendCampaignUpdate($campaign, $subscribers) {
        $data = [
            'subject' => 'Campaign Update: ' . $campaign['title'],
            'campaign_title' => $campaign['title'],
            'current_amount' => Utility::formatCurrency($campaign['current_amount']),
            'goal_amount' => Utility::formatCurrency($campaign['goal_amount']),
            'progress' => round(($campaign['current_amount'] / $campaign['goal_amount']) * 100),
            'days_left' => ceil((strtotime($campaign['end_date']) - time()) / (60 * 60 * 24))
        ];

        foreach ($subscribers as $subscriber) {
            $data['name'] = $subscriber['name'];
            $this->sendTemplate($subscriber['email'], 'campaign_update', $data);
        }
    }

    /**
     * Send event reminder
     */
    public function sendEventReminder($event, $registrants) {
        $data = [
            'subject' => 'Reminder: ' . $event['title'],
            'event_title' => $event['title'],
            'event_date' => date('F j, Y', strtotime($event['start_date'])),
            'event_time' => date('g:i A', strtotime($event['start_date'])),
            'event_location' => $event['location']
        ];

        foreach ($registrants as $registrant) {
            $data['name'] = $registrant['name'];
            $this->sendTemplate($registrant['email'], 'event_reminder', $data);
        }
    }

    /**
     * Send grant application status update
     */
    public function sendGrantApplicationStatus($application, $applicant) {
        $data = [
            'subject' => 'Grant Application Status Update',
            'name' => $applicant['name'],
            'grant_title' => $application['grant_title'],
            'status' => ucfirst($application['status']),
            'feedback' => $application['feedback'] ?? ''
        ];

        return $this->sendTemplate($applicant['email'], 'grant_status', $data);
    }

    /**
     * Send bulk email
     */
    public function sendBulkEmail($recipients, $subject, $content, $options = []) {
        // Split recipients into chunks to avoid server limitations
        $chunks = array_chunk($recipients, 50);
        
        foreach ($chunks as $chunk) {
            $bcc = implode(',', array_column($chunk, 'email'));
            $this->send($this->from, $subject, $content, [], array_merge($options, ['bcc' => $bcc]));
            
            // Add delay to prevent server overload
            sleep(1);
        }
    }
}