<?php

namespace App\Controller;

use App\Entity\Categories;
use App\Entity\Roles;
use App\Form\RolesType;
use App\Repository\RolesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/roles')]
class RolesController extends AbstractController
{
    #[Route('/', name: 'app_roles_index', methods: ['GET'])]
    public function index(RolesRepository $rolesRepository): Response
    {
        if (!$this->isUserAdmin()) {
            throw new AccessDeniedException('Access denied: You do not have permission to go there.');
        }
        return $this->render('roles/index.html.twig', [
            'roles' => $rolesRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_roles_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $role = new Roles();
        $form = $this->createForm(RolesType::class, $role);
        $form->handleRequest($request);
        $role->setCreatedAt(new \DateTimeImmutable());

        if ($form->isSubmitted() && $form->isValid()) {

            $entityManager->persist($role);
            $entityManager->flush();

            return $this->redirectToRoute('app_roles_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('roles/new.html.twig', [
            'role' => $role,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_roles_show', methods: ['GET'])]
    public function show(Roles $role): Response
    {
        return $this->render('roles/show.html.twig', [
            'role' => $role,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_roles_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Roles $role, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(RolesType::class, $role);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();
            // Récupérez les catégories sélectionnées à partir des données soumises
            $selectedCategories = $formData->getCategory();
            $selectedCategoryNames = [];
            foreach ($selectedCategories as $category) {
                $role->addCategory($category);
                $selectedCategoryNames[] = $category->getCategoryName();
            }


            // Sauvegardez le rôle, il sera mis à jour avec la nouvelle catégorie sélectionnée
            $entityManager->flush();
            return $this->redirectToRoute('app_roles_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('roles/edit.html.twig', [
            'role' => $role,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_roles_delete', methods: ['POST'])]
    public function delete(Request $request, Roles $role, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $role->getId(), $request->request->get('_token'))) {
            $entityManager->remove($role);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_roles_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/assign-category/{categoryId}', name: 'app_roles_assign_category')]
    public function assignCategory(Request $request, Roles $role, Categories $category, EntityManagerInterface $entityManager): Response
    {
        // Ajoutez le rôle à la catégorie s'il n'est pas déjà présent
        if (!$category->getRoles()->contains($role)) {
            $category->addRole($role);
            $role->addCategory($category); // Assurez-vous d'ajouter la catégorie au rôle
            $entityManager->persist($category);
            $entityManager->persist($role); // N'oubliez pas de persister le rôle
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_categories_show', ['id' => $category->getId()]);
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
