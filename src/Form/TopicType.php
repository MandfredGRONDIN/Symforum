<?php

namespace App\Form;

use App\Entity\Board;
use App\Entity\Topic;
use App\Repository\BoardRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Type de formulaire pour la création ou la modification d'un topic.
 */
class TopicType extends AbstractType
{
    private $boardRepository;
    private $security;

    public function __construct(BoardRepository $boardRepository, Security $security)
    {
        $this->boardRepository = $boardRepository;
        $this->security = $security;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // Récupère tous les boards depuis le dépôt
        $boards = $this->boardRepository->findAll();

        // Récupère l'utilisateur actuellement connecté
        $user = $this->security->getUser();

        // Crée un tableau vide pour les boards auxquels l'utilisateur a accès
        $accessibleBoards = [];

        // Vérifie si l'utilisateur a un rôle admin
        $isAdmin = $this->isUserAdmin($user);
        // Filtrer les boards auxquels l'utilisateur a accès en utilisant isUserAllowedToAccessBoard
        foreach ($boards as $board) {
            // Si l'utilisateur est admin, ajoutez tous les boards
            if ($isAdmin) {
                $accessibleBoards[$board->getBoardName()] = $board;
            } else {
                // Vérifiez si l'utilisateur a accès à ce board
                if ($this->isUserAllowedToAccessBoard($user, $board)) {
                    $accessibleBoards[$board->getBoardName()] = $board;
                }
            }
        }

        // Construit les champs du formulaire
        $builder
            ->add('title')
            ->add('content', TextareaType::class, [
                'attr' => ['class' => 'text-content'],
            ])
            ->add('board', ChoiceType::class, [
                'choices' => $accessibleBoards,
                'placeholder' => 'Choose a board',
                'required' => true,
            ])
            ->add('imageFile', FileType::class, [
                'label' => 'You want to add a picture?',
                'required' => false,
            ]);
    }

    /**
     * Configure les options par défaut pour le formulaire.
     *
     * @param OptionsResolver $resolver Le résolveur pour configurer les options.
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        // Définit la classe de données par défaut pour le formulaire
        $resolver->setDefaults([
            'data_class' => Topic::class,
        ]);
    }
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
    private function isUserAdmin(?UserInterface $user): bool
    {
        // Si l'utilisateur n'est pas connecté ou n'a pas l'un des rôles "admin" ou "insider", il n'a pas l'autorisation
        if (!$user || !in_array('admin', $user->getRoles())) {
            return false;
        }

        // L'utilisateur a l'un des rôles "admin" ou "insider" et est autorisé
        return true;
    }
}
