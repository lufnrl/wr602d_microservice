<?php

declare(strict_types=1);

namespace App\Service;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

class MailerService
{
    public function __construct(
        private readonly MailerInterface $mailer,
        private readonly string $noReplyEmail,
        private readonly string $replyEmail,
        private readonly string $fromName,
    ) {
    }

    private function buildEmail(string $to, string $subject, string $content, ?string $fromName = null): Email
    {
        $fromName = $fromName ?? $this->fromName;

        return (new Email())
            ->from(new Address($this->noReplyEmail, $fromName))
            ->replyTo($this->replyEmail)
            ->to($to)
            ->subject($subject)
            ->text($content)
            ->html('<p>'.$content.'</p>');
    }

    private function send(Email $email): void
    {
        $this->mailer->send($email);
    }

    public function sendEmail(string $to, string $subject, string $content, ?string $fromName = null): void
    {
        $email = $this->buildEmail($to, $subject, $content, $fromName);
        $this->send($email);
    }
}
