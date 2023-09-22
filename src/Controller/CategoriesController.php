<?php

namespace App\Controller;

use App\Entity\Board;
use App\Entity\Categories;
use App\Entity\Roles;
use App\Form\CategoriesType;
use App\Repository\BoardRepository;
use App\Repository\CategoriesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;


#[Route('/categories')]
class CategoriesController extends AbstractController
{
    #[Route('/', name: 'app_categories_index', methods: ['GET'])]
    public function index(CategoriesRepository $categoriesRepository): Response
    {
        // Récupérez toutes les catégories depuis le repository
        $categories = $categoriesRepository->findAll();

        // Si l'utilisateur est un administrateur, affichez toutes les catégories
        if (!$this->isUserAdmin()) {
            throw new AccessDeniedException('Access denied: You do not have permission to go there.');
        }

        return $this->render('categories/index.html.twig', [
            'categories' => $categories,
        ]);
    }

    #[Route('/new', name: 'app_categories_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        /**
         * Crée une nouvelle catégorie.
         *
         * @param Request $request La requête HTTP
         * @param EntityManagerInterface $entityManager Le gestionnaire d'entités
         * @return Response La réponse HTTP
         */
        // Vérifiez si l'utilisateur est administrateur avant de permettre l'accès
        if (!$this->isUserAdmin($this->getUser())) {
            throw new AccessDeniedException('Access denied: You do not have permission to create a new category.');
        }
        $category = new Categories();
        $form = $this->createForm(CategoriesType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $selectedRoles = $form->get('roles')->getData();

            // Créez la catégorie
            $entityManager->persist($category);
            $entityManager->flush();

            // Assignez les rôles à la catégorie
            foreach ($selectedRoles as $role) {
                $this->forward('App\Controller\CategoriesController::assignRole', [
                    'request' => $request,
                    'role' => $role,
                    'category' => $category,
                ]);
            }

            return $this->redirectToRoute('app_categories_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('categories/new.html.twig', [
            'category' => $category,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_categories_show', methods: ['GET'])]
    public function show(Categories $category): Response
    {
        /**
         * Affiche les détails d'une catégorie.
         *
         * @param Categories $category La catégorie à afficher
         * @return Response La réponse HTTP contenant les détails du board
         */
        if (!$this->isUserAllowedToAccessCategory($category) && !$this->isUserAdmin()) {
            // L'utilisateur n'est pas autorisé à accéder à cette catégorie
            throw new AccessDeniedException('Access denied: You do not have permission to access this category.');
        }

        return $this->render('categories/show.html.twig', [
            'category' => $category,
        ]);
    }


    #[Route('/{id}/edit', name: 'app_categories_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Categories $category, EntityManagerInterface $entityManager): Response
    {
        /**
         * Modifie une catégorie existant.
         *
         * @param Request $request La requête HTTP
         * @param Categories $category La catégorie à modifier
         * @param EntityManagerInterface $entityManager Le gestionnaire d'entités
         * @return Response La réponse HTTP
         */
        // Vérifiez si l'utilisateur est administrateur avant de permettre l'accès
        if (!$this->isUserAdmin($this->getUser())) {
            throw new AccessDeniedException('Access denied: You do not have permission to edit a category.');
        }

        $form = $this->createForm(CategoriesType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $selectedRoles = $form->get('roles')->getData();

            $entityManager->flush();
            // Assignez les rôles à la catégorie
            foreach ($selectedRoles as $role) {
                $this->forward('App\Controller\CategoriesController::assignRole', [
                    'request' => $request,
                    'role' => $role,
                    'category' => $category,
                ]);
            }

            return $this->redirectToRoute('app_categories_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('categories/edit.html.twig', [
            'category' => $category,
            'form' => $form->createView(),
        ]);
    }


    #[Route('/{id}', name: 'app_categories_delete', methods: ['POST'])]
    public function delete(Request $request, Categories $category, EntityManagerInterface $entityManager): Response
    {
        /**
         * Supprime une catégorie.
         *
         * @param Request $request La requête HTTP
         * @param Categories $category La catégorie à supprimer
         * @param EntityManagerInterface $entityManager Le gestionnaire d'entités
         * @return Response La réponse HTTP
         */
        // Vérifiez si l'utilisateur est administrateur avant de permettre l'accès
        if (!$this->isUserAdmin($this->getUser())) {
            throw new AccessDeniedException('Access denied: You do not have permission to delete a category.');
        }
        if ($this->isCsrfTokenValid('delete' . $category->getId(), $request->request->get('_token'))) {
            $entityManager->remove($category);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_categories_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/assign-role/{roleId}', name: 'app_categories_assign_role')]
    public function assignRole(Request $request, Roles $role, Categories $category, EntityManagerInterface $entityManager): Response
    {

        if ($category->getRoles()->contains($role)) {
            $category->addRole($role);
            $role->addCategory($category);
            $entityManager->persist($role);
            $entityManager->persist($category);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_categories_show', ['id' => $category->getId()]);
    }

    /**
     * Vérifie si l'utilisateur a accès à un board en fonction de ses rôles et des catégories associées.
     *
     * @param UserInterface|null $user L'utilisateur connecté ou null s'il n'est pas connecté
     * @param Board $board Le board auquel l'utilisateur souhaite accéder
     * @return bool true si l'utilisateur est autorisé, sinon false
     */
    private function isUserAllowedToAccessCategory(Categories $category): bool
    {

        $user = $this->getUser();
        // Si l'utilisateur n'est pas connecté, il n'a pas accès à la catégorie
        if (!$user) {
            return false;
        }

        // Récupérez les rôles de l'utilisateur
        $userRoles = $user->getRoles();

        // Vérifiez si l'utilisateur a l'un des rôles associés à cette catégorie
        foreach ($category->getRoles() as $role) {
            if (in_array($role->getRoleName(), $userRoles)) {
                // L'utilisateur a accès à cette catégorie
                return true;
            }
        }

        // L'utilisateur n'a pas accès à la catégorie s'il ne correspond à aucun rôle associé
        return false;
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
