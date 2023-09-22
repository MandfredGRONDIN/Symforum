<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Type de formulaire pour l'inscription d'un nouvel utilisateur.
 */
class RegistrationFormType extends AbstractType
{
    /**
     * Construit le formulaire d'inscription.
     *
     * @param FormBuilderInterface $builder Le constructeur de formulaire.
     * @param array $options Les options du formulaire.
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
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
            ->add('agreeTerms', CheckboxType::class, [
                'mapped' => false,
                'constraints' => [
                    new IsTrue([
                        'message' => 'You must accept our conditions.',
                    ]),
                ],
            ])
            ->add('plainPassword', PasswordType::class, [
                'mapped' => false,
                'attr' => ['autocomplete' => 'new-password'],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Password',
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Your password must be at least {{ limit }} characters long',
                        'max' => 4096,
                    ]),
                ],
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
