<?php
namespace EmailCampaign\Infrastructure\Services;

use EmailCampaign\Domain\Interfaces\EmailServiceInterface;
use EmailCampaign\Config\Config;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class EmailService implements EmailServiceInterface
{
    private $logger;

    public function __construct()
    {
        $this->logger = new Logger();
    }

    private function createMailer(): PHPMailer
    {
        $config = Config::getInstance()->get('mail');

        $mailer = new PHPMailer(true);

        $mailer->isSMTP();
        $mailer->Host = $config['host'] ?? 'smtp.gmail.com';
        $mailer->SMTPAuth = true;
        $mailer->Username = $config['username'] ?? 'noreply@example.com';
        $mailer->Password = $config['password'] ?? '';
        $mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mailer->Port = $config['port'] ?? 587;

        $fromAddress = $config['from']['address'] ?? $config['username'] ?? 'noreply@example.com';
        $fromName = $config['from']['name'] ?? 'Email Campaign';

        $mailer->setFrom($fromAddress, $fromName);

        $mailer->isHTML(true);
        $mailer->CharSet = PHPMailer::CHARSET_UTF8;

        return $mailer;
    }

    public function send(string $email, string $subject, string $template, array $data = []): bool
    {
        try {
            $mailer = $this->createMailer();

            $mailer->addAddress($email);
            $mailer->Subject = $subject;
            $mailer->Body = $this->renderTemplate($template, $data);
            $mailer->AltBody = strip_tags($mailer->Body);

            $result = $mailer->send();

            if ($result) {
                $this->logger->info("Email sent successfully", [
                    'email' => $email,
                    'subject' => $subject
                ]);
            } else {
                $this->logger->warning("Email sending failed (no exception)", [
                    'email' => $email,
                    'subject' => $subject,
                    'smtp_error' => $mailer->ErrorInfo
                ]);
            }

            return $result;

        } catch (Exception $e) {
            $smtpError = $mailer->ErrorInfo ?? 'N/A';
            $this->logger->error("Email send error", [
                'email' => $email,
                'subject' => $subject,
                'error' => $e->getMessage(),
                'smtp_error' => $smtpError
            ]);

            return false;
        }
    }

    private function renderTemplate(string $template, array $data): string
    {
        $templateFile = __DIR__ . '/../../../templates/' . $template . '.php';

        if (!file_exists($templateFile)) {
            throw new \RuntimeException("Template file not found: {$templateFile}");
        }

        $user = $data['user'] ?? (object)['email' => 'test@example.com'];
        $unsubscribe_link = $data['unsubscribe_link'] ?? '#';
        $current_date = $data['current_date'] ?? date('Y');

        ob_start();
        include $templateFile;
        return ob_get_clean();
    }
}