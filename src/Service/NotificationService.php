<?php

namespace App\Service;

use App\Repository\UserRepository;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Psr\Log\LoggerInterface;

class NotificationService
{
    private const EXPO_PUSH_URL = 'https://exp.host/--/api/v2/push/send';

    public function __construct(
        private HttpClientInterface $httpClient,
        private UserRepository $userRepository,
        private LoggerInterface $logger
    ) {
    }

    public function sendNotificationToUsers(
        array $userIds,
        string $title,
        string $body,
        ?array $data = null
    ): array {
        $users = $this->userRepository->findBy(['id' => $userIds]);
        $results = [];

        foreach ($users as $user) {
            if ($user->getExpoToken()) {
                $result = $this->sendSingleNotification(
                    $user->getExpoToken(),
                    $title,
                    $body,
                    $data
                );
                $results[$user->getId()] = $result;
            } else {
                $results[$user->getId()] = [
                    'success' => false,
                    'error' => 'No expo token found for user'
                ];
            }
        }

        return $results;
    }

    public function sendNotificationToUser(
        int $userId,
        string $title,
        string $body,
        ?array $data = null
    ): array {
        return $this->sendNotificationToUsers([$userId], $title, $body, $data);
    }

    private function sendSingleNotification(
        string $expoToken,
        string $title,
        string $body,
        ?array $data = null
    ): array {
        try {
            $payload = [
                'to' => $expoToken,
                'title' => $title,
                'body' => $body
            ];

            if ($data !== null) {
                $payload['data'] = $data;
            }

            $response = $this->httpClient->request('POST', self::EXPO_PUSH_URL, [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'User-Agent' => 'Flow-Back-PHP'
                ],
                'json' => $payload
            ]);

            $statusCode = $response->getStatusCode();
            $content = $response->getContent();

            if ($statusCode === 200) {
                $this->logger->info('Notification sent successfully', [
                    'token' => $expoToken,
                    'title' => $title
                ]);

                return [
                    'success' => true,
                    'response' => json_decode($content, true)
                ];
            } else {
                $this->logger->error('Failed to send notification', [
                    'token' => $expoToken,
                    'status_code' => $statusCode,
                    'response' => $content
                ]);

                return [
                    'success' => false,
                    'error' => 'HTTP ' . $statusCode,
                    'response' => $content
                ];
            }
        } catch (\Exception $e) {
            $this->logger->error('Exception while sending notification', [
                'token' => $expoToken,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    public function sendBulkNotifications(array $notifications): array
    {
        $payloads = [];
        $userTokenMap = [];

        foreach ($notifications as $notification) {
            $userIds = $notification['userIds'];
            $title = $notification['title'];
            $body = $notification['body'];
            $data = $notification['data'] ?? null;

            $users = $this->userRepository->findBy(['id' => $userIds]);

            foreach ($users as $user) {
                if ($user->getExpoToken()) {
                    $payload = [
                        'to' => $user->getExpoToken(),
                        'title' => $title,
                        'body' => $body
                    ];

                    if ($data !== null) {
                        $payload['data'] = $data;
                    }

                    $payloads[] = $payload;
                    $userTokenMap[$user->getExpoToken()] = $user->getId();
                }
            }
        }

        if (empty($payloads)) {
            return ['success' => false, 'error' => 'No valid tokens found'];
        }

        try {
            $response = $this->httpClient->request('POST', self::EXPO_PUSH_URL, [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'User-Agent' => 'Flow-Back-PHP'
                ],
                'json' => $payloads
            ]);

            $statusCode = $response->getStatusCode();
            $content = $response->getContent();

            if ($statusCode === 200) {
                $this->logger->info('Bulk notifications sent successfully', [
                    'count' => count($payloads)
                ]);

                return [
                    'success' => true,
                    'response' => json_decode($content, true),
                    'sent_count' => count($payloads)
                ];
            } else {
                $this->logger->error('Failed to send bulk notifications', [
                    'status_code' => $statusCode,
                    'response' => $content
                ]);

                return [
                    'success' => false,
                    'error' => 'HTTP ' . $statusCode,
                    'response' => $content
                ];
            }
        } catch (\Exception $e) {
            $this->logger->error('Exception while sending bulk notifications', [
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}