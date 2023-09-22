<?php

namespace App\Form;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use App\Entity\Roles;
use App\Entity\User;
use App\Repository\RolesRepository;
use App\Repository\UserRepository;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class UserType extends AbstractType
{
    private $rolesRepository;

    /**
     * Constructeur pour TopicType.
     *
     * @param BoardRepository $boardRepository Le dépôt pour récupérer les board.
     */
    public function __construct(RolesRepository $rolesRepository)
    {
        $this->rolesRepository = $rolesRepository;
    }
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // Récupère tous les boards depuis le dépôt
        $roles = $this->rolesRepository->findAll();

        // Crée un board de choix pour la sélection de board
        $choicesRole = [];
        foreach ($roles as $role) {
            $choicesRoles[$role->getRoleName()] = $role;
        }
        // Ajout des champs du formulaire
        $builder
            ->add('lastname', TextType::class, [
                'label' => 'Last name',
                'required' => false,
                'attr' => ['maxlength' => 50],
            ])
            ->add('firstname', TextType::class, [
                'label' => 'First name',
                'required' => false,
                'attr' => ['maxlength' => 50],
            ])
            ->add('pseudo', TextType::class, [
                'label' => 'Pseudo',
                'attr' => ['maxlength' => 50],
            ])
            ->add('email', EmailType::class, [
                'label' => 'E-mail',
            ])
            ->add('roles', ChoiceType::class, [
                'choices' => $choicesRoles,
                'placeholder' => 'Choose a role',
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
        // Définit la classe de données par défaut pour le formulaire
        $resolver->setDefaults([
            'data_class' => User::class, // Le modèle de données lié au formulaire
        ]);
    }
}
