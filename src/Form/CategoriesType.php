<?php

namespace App\Form;

use App\Entity\Categories;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use App\Entity\Roles;

/**
 * Type de formulaire pour la création ou la modification d'une catégorie.
 */
class CategoriesType extends AbstractType
{
    /**
     * Construit le formulaire de catégorie.
     *
     * @param FormBuilderInterface $builder Le constructeur de formulaire.
     * @param array $options Les options du formulaire.
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // Ajout du champ pour le nom de la catégorie
        $builder
            ->add('category_name', TextType::class, [
                'label' => 'Category name',
                'required' => true,
                'attr' => ['maxlength' => 255],
            ])
            ->add('roles', EntityType::class, [
                'class' => Roles::class,
                'choice_label' => 'role_name',
                'multiple' => true,
                'expanded' => true,
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
            'data_class' => Categories::class, // Le modèle de données lié au formulaire
        ]);
    }
}
