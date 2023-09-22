<?php

namespace App\Controller;

use App\Repository\CategoriesRepository;
use App\Repository\RolesRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    private $rolesRepository;

    public function __construct(RolesRepository $rolesRepository)
    {
        $this->rolesRepository = $rolesRepository;
    }

    #[Route('/home', name: 'app_home')]
    public function index(CategoriesRepository $categoriesRepository): Response
    {
        // Récupérez l'utilisateur actuellement connecté
        $user = $this->getUser();

        // Récupérez les rôles de l'utilisateur, s'il est connecté
        $userRoles = $user ? $user->getRoles() : [];

        // Utilisez le repository pour récupérer les données de rôles si nécessaire
        $rolesData = $this->rolesRepository->findAll();

        // Initialisez un tableau vide pour stocker les catégories filtrées
        $filteredCategories = [];

        // Vérifiez si l'utilisateur a le rôle "admin"
        if (in_array('admin', $userRoles, true)) {
            // Si l'utilisateur a le rôle "admin", chargez toutes les catégories
            $filteredCategories = $categoriesRepository->findAll();
        } else {
            // Parcourez les catégories
            $categories = $categoriesRepository->findAll();
            foreach ($categories as $category) {
                // Vérifiez si la catégorie est associée à au moins l'un des rôles de l'utilisateur
                foreach ($category->getRoles() as $role) {
                    if (in_array($role->getRoleName(), $userRoles)) {
                        // Si oui, ajoutez cette catégorie aux catégories filtrées
                        $filteredCategories[] = $category;
                        break; // Sortez de la boucle intérieure, pas besoin de vérifier d'autres rôles pour cette catégorie
                    }
                }
            }
        }

        return $this->render('home/index.html.twig', [
            'categories' => $filteredCategories, // Utilisez les catégories filtrées pour l'affichage
            'userRoles' => $userRoles,
            'rolesData' => $rolesData,
        ]);
    }
}
