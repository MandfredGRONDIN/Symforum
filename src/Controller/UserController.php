<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

#[Route('/user')]
class UserController extends AbstractController
{
    #[Route('/', name: 'app_user_index', methods: ['GET'])]
    public function index(UserRepository $userRepository): Response
    {


        if (!$this->isUserAdmin()) {
            throw new AccessDeniedException('Access denied: You do not have permission to go there.');
        }
        return $this->render('user/index.html.twig', [
            'users' => $userRepository->findAll(),
        ]);
    }


    #[Route('/{id}', name: 'app_user_show', methods: ['GET'])]
    public function show(User $user): Response
    {
        if (!$this->isUserAdmin()) {
            throw new AccessDeniedException('Access denied: You do not have permission to go there.');
        }
        return $this->render('user/show.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_user_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {

        if (!$this->isUserAdmin()) {
            throw new AccessDeniedException('Access denied: You do not have permission to go there.');
        }
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Récupérer les rôles sélectionnés à partir du formulaire
            $selectedRoles = $form->get('roles')->getData();


            // Mettre à jour les rôles de l'utilisateur
            $user->setRoles($selectedRoles);

            $entityManager->flush();

            return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('user/edit.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }


    #[Route('/{id}', name: 'app_user_delete', methods: ['POST'])]
    public function delete(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        if (!$this->isUserAdmin()) {
            throw new AccessDeniedException('Access denied: You do not have permission to go there.');
        }
        if ($this->isCsrfTokenValid('delete' . $user->getId(), $request->request->get('_token'))) {
            $entityManager->remove($user);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
    }
    private function isUserAdmin(): bool
    {
        // Récupérez l'utilisateur connecté
        $user = $this->getUser();
        // Si l'utilisateur n'est pas connecté ou s'il n'a pas le rôle "admin", il n'a pas l'autorisation
        if (!$user || !in_array('admin', $user->getRoles())) {
            return false;
        }

        // L'utilisateur a le rôle "admin" et est autorisé
        return true;
    }
}
