<?php

namespace App\Form;

use App\Entity\Board;
use App\Entity\Categories;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use App\Repository\CategoriesRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Type de formulaire pour la création ou la modification d'un board.
 */
class BoardType extends AbstractType
{
    private $categoriesRepository;
    private $security;

    /**
     * Constructeur du formulaire de board.
     *
     * @param CategoriesRepository $categoriesRepository Le repository des catégories.
     */
    public function __construct(CategoriesRepository $categoriesRepository, Security $security)
    {
        $this->categoriesRepository = $categoriesRepository;
        $this->security = $security;
    }

    /**
     * Construit le formulaire de board.
     *
     * @param FormBuilderInterface $builder Le constructeur de formulaire.
     * @param array $options Les options du formulaire.
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // Récupérer les catégories depuis la base de données
        $categories = $this->categoriesRepository->findAll();

        // Récupère l'utilisateur actuellement connecté
        $user = $this->security->getUser();

        $choices = [];

        // Vérifie si l'utilisateur a un rôle admin
        $isAllowed = $this->isUserAllowed($user);

        foreach ($categories as $category) {
            if ($isAllowed) {
                $choices[$category->getCategoryName()] = $category;
            } else {
                if ($this->isUserAllowedToAccessCategories($user, $category)) {
                    $choices[$category->getCategoryName()] = $category;
                }
            }
        }

        // Ajout des champs pour le nom du board et la catégorie
        $builder
            ->add('board_name')
            ->add('category', ChoiceType::class, [
                'choices' => $choices,
                'placeholder' => 'Choose a category',
                'required' => true,
            ]);
    }

    /**
     * Configure les options par défaut pour le formulaire.
     *
     * @param OptionsResolver $resolver Le résolveur pour configurer les options.
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Board::class,
        ]);
    }
    private function isUserAllowedToAccessCategories(?UserInterface $user, Categories $category): bool
    {
        // Si l'utilisateur n'est pas connecté, il n'a pas accès au board
        if (!$user) {
            return false;
        }

        // Récupérez les rôles de l'utilisateur
        $userRoles = $user->getRoles();

        // Récupérez les rôles de la catégorie associée à ce board
        $categoryRoles = $category->getRoles();

        // Vérifiez si l'utilisateur a l'un des rôles associés à cette catégorie
        foreach ($categoryRoles as $role) {
            if (in_array($role->getRoleName(), $userRoles)) {
                // L'utilisateur a accès à la catégorie associée à ce board
                return true;
            }
        }

        // L'utilisateur n'a pas accès à la catégorie s'il ne correspond à aucun rôle associé
        return false;
    }

    private function isUserAllowed(?UserInterface $user): bool
    {
        // Si l'utilisateur n'est pas connecté ou n'a pas l'un des rôles "admin" ou "insider", il n'a pas l'autorisation
        if (!$user || !in_array('admin', $user->getRoles())) {
            return false;
        }

        // L'utilisateur a l'un des rôles "admin" ou "insider" et est autorisé
        return true;
    }
}
