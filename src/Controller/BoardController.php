<?php

namespace App\Controller;

use App\Entity\Board;
use App\Form\BoardType;
use App\Repository\BoardRepository;
use App\Repository\TopicRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

#[Route('/board')]
class BoardController extends AbstractController
{
    #[Route('/', name: 'app_board_index', methods: ['GET'])]
    public function index(BoardRepository $boardRepository): Response
    {
        // Récupérez tous les boards depuis le repository
        $boards = $boardRepository->findAll();

        // Si l'utilisateur est un administrateur, affichez tous les boards
        if (!$this->isUserAllowed()) {
            throw new AccessDeniedException('Access denied: You do not have permission to go there.');
        }
        return $this->render('board/index.html.twig', [
            'boards' => $boards,
        ]);
    }



    #[Route('/new', name: 'app_board_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        /**
         * Crée un nouveau board.
         *
         * @param Request $request La requête HTTP
         * @param EntityManagerInterface $entityManager Le gestionnaire d'entités
         * @return Response La réponse HTTP
         */
        // Vérifiez si l'utilisateur est administrateur ou insider avant de permettre l'accès
        if (!$this->isUserAllowed()) {
            throw new AccessDeniedException('Access denied: You do not have permission to create a new board.');
        }
        $board = new Board();
        $form = $this->createForm(BoardType::class, $board);
        $form->handleRequest($request);

        $board->setCreatedAt(new \DateTimeImmutable());

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($board);
            $entityManager->flush();

            return $this->redirectToRoute('app_board_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('board/new.html.twig', [
            'board' => $board,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_board_show', methods: ['GET'])]
    public function show(Board $board, TopicRepository $topicRepository): Response
    {
        /**
         * Affiche les détails d'un board.
         *
         * @param Board $board Le board à afficher
         * @return Response La réponse HTTP contenant les détails du board
         */
        $user = $this->getUser();
        if (!$this->isUserAllowedToAccessBoard($user, $board) && !$this->isUserAllowed()) {
            // L'utilisateur n'est pas autorisé à accéder à ce board
            throw new AccessDeniedException('Access denied: You do not have permission to access this board.');
        }
        $topic = $topicRepository->findAll();
        return $this->render('board/show.html.twig', [
            'board' => $board,
            'topic' => $topic,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_board_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Board $board, EntityManagerInterface $entityManager): Response
    {
        /**
         * Modifie un board existant.
         *
         * @param Request $request La requête HTTP
         * @param Board $board Le board à modifier
         * @param EntityManagerInterface $entityManager Le gestionnaire d'entités
         * @return Response La réponse HTTP
         */
        // Vérifiez si l'utilisateur est administrateur ou insider avant de permettre l'accès
        if (!$this->isUserAllowed()) {
            throw new AccessDeniedException('Access denied: You do not have permission to create a new board.');
        }
        $form = $this->createForm(BoardType::class, $board);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_board_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('board/edit.html.twig', [
            'board' => $board,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_board_delete', methods: ['POST'])]
    public function delete(Request $request, Board $board, EntityManagerInterface $entityManager): Response
    {
        /**
         * Supprime un board.
         *
         * @param Request $request La requête HTTP
         * @param Board $board Le board à supprimer
         * @param EntityManagerInterface $entityManager Le gestionnaire d'entités
         * @return Response La réponse HTTP
         */
        // Vérifiez si l'utilisateur est administrateur ou insider avant de permettre l'accès
        if (!$this->isUserAllowed()) {
            throw new AccessDeniedException('Access denied: You do not have permission to create a new board.');
        }
        if ($this->isCsrfTokenValid('delete' . $board->getId(), $request->request->get('_token'))) {
            $entityManager->remove($board);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_board_index', [], Response::HTTP_SEE_OTHER);
    }
    /**
     * Vérifie si l'utilisateur a accès à un board en fonction de ses rôles et de la catégorie associée.
     *
     * @param UserInterface|null $user L'utilisateur connecté ou null s'il n'est pas connecté
     * @param Board $board Le board auquel l'utilisateur souhaite accéder
     * @return bool true si l'utilisateur est autorisé, sinon false
     */
    private function isUserAllowedToAccessBoard(?UserInterface $user, Board $board): bool
    {
        // Si l'utilisateur n'est pas connecté, il n'a pas accès au board
        if (!$user) {
            return false;
        }

        // Récupérez les rôles de l'utilisateur
        $userRoles = $user->getRoles();

        // Récupérez la catégorie associée à ce board
        $category = $board->getCategory();

        // Vérifiez si l'utilisateur a l'un des rôles associés à cette catégorie
        foreach ($category->getRoles() as $role) {
            if (in_array($role->getRoleName(), $userRoles)) {
                // L'utilisateur a accès à la catégorie associée à ce board
                return true;
            }
        }

        // L'utilisateur n'a pas accès au board s'il ne correspond à aucun rôle associé à la catégorie
        return false;
    }
    private function isUserAllowed(): bool
    {
        // Récupérez l'utilisateur connecté
        $user = $this->getUser();
        // Si l'utilisateur n'est pas connecté ou n'a pas l'un des rôles "admin" ou "insider", il n'a pas l'autorisation
        if (!$user || !in_array('admin', $user->getRoles()) && !in_array('insider', $user->getRoles())) {
            return false;
        }

        // L'utilisateur a l'un des rôles "admin" ou "insider" et est autorisé
        return true;
    }
}
