<?php

namespace App\Form;

use App\Entity\Message;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class MessageType extends AbstractType
{
    private $generator;

    public function __construct(UrlGeneratorInterface $generator)
    {
        $this->generator = $generator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $topicId = $options['topicId'];
        $messageId = $options['messageId']; // Récupérer l'ID du message actuel

        // Condition pour déterminer l'action en fonction de l'existence de l'ID du message
        if ($messageId) {
            $action = $this->generator->generate('app_message_edit', ['id' => $messageId]);
        } else {
            $action = $this->generator->generate('app_message_new', ['id' => $topicId]);
        }

        $builder
            ->setAction($action) // Utiliser l'action déterminée
            ->add('content', TextareaType::class, [
                'attr' => ['class' => 'text-content'],
            ])
            ->add('topic', HiddenType::class, [
                'data' => $topicId,
                'mapped' => false,
            ])
            ->add('imageFile', FileType::class, [
                'label' => 'you want to add a picture?',
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Message::class,
            'topicId' => null,
            'messageId' => null, // Ajouter l'option pour l'ID du message
        ]);
    }
}
