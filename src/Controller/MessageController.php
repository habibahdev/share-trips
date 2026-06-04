<?php

namespace App\Controller;

use App\Entity\Conversation;
use App\Entity\Message;
use App\Entity\Trip;
use App\Entity\User;
use App\Repository\ConversationRepository;
use App\Repository\MessageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Contrôleur responsable de la gestion des messages.
 */
#[Route('/conversations', name: 'app_conversation_')]
final class MessageController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private MessageRepository $messageRepository,
        private ConversationRepository $conversationRepository
    ) {
    }

    #[Route('/open/{tripId}', name: 'open', methods: ['GET', 'POST'])]
    public function open(int $tripId): Response
    {
        $trip = $this->entityManager->find(Trip::class, $tripId);
        $user = $this->getUser();
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        $conversation = $this->conversationRepository->findOneBy(['trip' => $trip, 'passenger' => $user]);
        if (!$conversation) {
            $conversation = new Conversation();
            $conversation->setTrip($trip)
                ->setDriver($trip->getDriver())
                ->setPassenger($user)
                ->setCreatedAt(new \DateTimeImmutable());
            $this->entityManager->persist($conversation);
            $this->entityManager->flush();
        }
        return $this->redirectToRoute('app_conversation_show', [
            'id' => $conversation->getId()
        ]);
    }

    #[Route('/{id}', name: 'show')]
    public function show(Conversation $conversation): Response
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }
        $this->denyAccessUnlessGranted('CONVERSATION_VIEW', $conversation);
        $this->messageRepository->markAsReadInConversation($conversation, $user);
        return $this->render('conversation/show.html.twig', [
            'conversation' => $conversation
        ]);
    }

    #[Route('/{id}/messages', name: 'send', methods: ['POST'])]
    public function send(Conversation $conversation, Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted('CONVERSATION_VIEW', $conversation);
        $content = trim((string) $request->request->get('content', ''));
        if (!$content) {
            return $this->json(['error' => 'Message vide'], 400);
        }
        $user = $this->getUser();
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }
        $message = new Message();
        $message->setConversation($conversation)
            ->setSender($user)
            ->setContent($content)
            ->setSentAt(new \DateTimeImmutable());
        $this->entityManager->persist($message);
        $this->entityManager->flush();

        return $this->json(['status' => 'ok', 'id' => $message->getId()]);
    }

    #[Route('/', name: 'list')]
    public function list(PaginatorInterface $paginator, Request $request): Response
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }
        $conversations = $paginator->paginate(
            $this->conversationRepository->findAllForUserQuery($user),
            $request->query->getInt('page', 1),
            6
        );
        return $this->render('conversation/index.html.twig', [
            'conversations' => $conversations
        ]);
    }

    #[Route('/{id}/poll', name: 'poll', methods: ['GET'])]
    public function poll(Conversation $conversation, Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted('CONVERSATION_VIEW', $conversation);
        $user = $this->getUser();
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }
        $lastId = $request->query->getInt('lastId', 0);
        $messages = $this->messageRepository->findNewerThan($conversation, $lastId);
        $this->messageRepository->markAsReadInConversation($conversation, $user);
        $data = array_map(fn(Message $m) => [
            'id' => $m->getId(),
            'content' => $m->getContent(),
            'senderId' => $m->getSender()->getId(),
            'senderName' => $m->getSender()->getFirstName(),
            'sentAt' => $m->getSentAt()->format('H:i'),
        ], $messages);
        return $this->json($data);
    }

    #[Route('/unread-count', name: 'unread_count', methods: ['GET'])]
    public function unreadCount(): JsonResponse
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }
        return $this->json([
            'count' => $this->messageRepository->countUnreadForUser($user)
        ]);
    }
}
