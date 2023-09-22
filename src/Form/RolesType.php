<?php

namespace App\Form;

use App\Entity\Roles;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use App\Entity\Categories;

class RolesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('role_name')
            ->add('category', EntityType::class, [
                'class' => Categories::class,
                'choice_label' => 'category_name',
                'multiple' => true,
                'expanded' => true,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Roles::class,
        ]);
    }
}
