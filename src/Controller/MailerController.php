<?php

namespace App\Controller;

use App\Service\MailerService;
use App\Service\Utils\RequestChecker;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class MailerController extends AbstractController
{
    public function __construct(
        private readonly MailerService $mailerService,
        private readonly RequestChecker $requestChecker,
    ) {
    }

    #[Route('/send-mail', name: 'send_mail', methods: [Request::METHOD_POST])]
    public function sendMail(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $missingFields = $this->requestChecker->checkRequiredFields($data ?? [], ['to', 'subject', 'message']);
        if ($missingFields) {
            return $missingFields;
        }

        $emailError = $this->requestChecker->checkEmail($data['to']);
        if ($emailError) {
            return $emailError;
        }

        try {
            $this->mailerService->sendEmail(
                $data['to'],
                $data['subject'],
                $data['message'],
                $data['fromName'] ?? null
            );

            return new JsonResponse(['message' => 'Mail sent!', 'status' => 200]);
        } catch (\Exception $e) {
            return new JsonResponse(['message' => 'Failed to send mail.', 'status' => 500], 500);
        }
    }
}
