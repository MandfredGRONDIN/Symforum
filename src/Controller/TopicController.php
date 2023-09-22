<?php

namespace App\Controller;

use App\Entity\Message;
use App\Entity\Topic;
use App\Form\MessageType;
use App\Form\TopicType;
use App\Repository\MessageRepository;
use App\Repository\TopicRepository;
use App\Service\FileUploader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/topic')]
class TopicController extends AbstractController
{
    #[Route('/', name: 'app_topic_index', methods: ['GET'])]
    public function index(TopicRepository $topicRepository): Response
    {
        /**
         * Affiche la liste des topics.
         *
         * @param TopicRepository $topicRepository Le repository des topic
         * @return Response La réponse HTTP contenant la liste des topic
         */
        return $this->render('topic/index.html.twig', [
            'topics' => $topicRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_topic_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $topic = new Topic();
        $form = $this->createForm(TopicType::class, $topic);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Récupérer l'utilisateur actuellement connecté
            $user = $this->getUser();

            // Gérer le téléchargement de l'image ici
            $imageFile = $topic->getImageFile();

            if ($imageFile) {

                // Générer un nom de fichier unique pour l'image
                $fileName =  $user->getId() . '_' . uniqid() . '.' . $imageFile->getClientOriginalExtension();

                // Déplacez le fichier téléchargé vers le répertoire de destination
                $imageFile->move(
                    $this->getParameter('images_directory'), // Remplacez par le chemin approprié
                    $fileName
                );

                // Stockez le nom du fichier dans l'entité Topic
                $topic->setImageFileName($fileName);
            }

            $topic->setCreatedAt(new \DateTimeImmutable());
            $topic->setUser($this->getUser());

            $entityManager->persist($topic);
            $entityManager->flush();

            return $this->redirectToRoute('app_topic_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('topic/new.html.twig', [
            'topic' => $topic,
            'form' => $form->createView(),
        ]);
    }


    #[Route('/{id}', name: 'app_topic_show', methods: ['GET'])]
    public function show(Request $request,  EntityManagerInterface $entityManager, Topic $topic, MessageRepository $messageRepository, $id): Response
    {
        /**
         * Affiche les détails d'un topic.
         *
         * @param Topic $topic Le topic à afficher
         * @return Response La réponse HTTP contenant les détails du topic
         */

        $messages = $messageRepository->findAll();
        $message = new Message();

        $formMessage = $this->createForm(MessageType::class, $message, [
            'topicId' => $topic->getId(), // Utilisez l'ID du sujet
        ]);


        $formMessage->handleRequest($request);

        $message->setCreatedAt(new \DateTimeImmutable());

        $message->setUser($this->getUser());

        if ($formMessage->isSubmitted() && $formMessage->isValid()) {
            $entityManager->persist($message);
            $entityManager->flush();
            return $this->redirectToRoute('app_topic_show', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('topic/show.html.twig', [
            'topic' => $topic,
            'messages' => $messages,
            'form' => $formMessage->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'app_topic_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Topic $topic, EntityManagerInterface $entityManager, FileUploader $fileUploader): Response
    {
        $form = $this->createForm(TopicType::class, $topic);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Vérifiez si un nouveau fichier image est téléchargé
            $newImageFile = $topic->getImageFile();

            if ($newImageFile) {
                // Supprimez l'ancienne image si elle existe
                $oldImageFileName = $topic->getImageFileName();
                if ($oldImageFileName) {
                    $oldImagePath = $this->getParameter('images_directory') . '/' . $oldImageFileName;
                    if (file_exists($oldImagePath)) {
                        unlink($oldImagePath);
                    }
                }

                // Générez un nom de fichier unique pour la nouvelle image
                $fileName = $topic->getUser()->getId() . '_' . uniqid() . '.' . $newImageFile->getClientOriginalExtension();

                // Déplacez le fichier téléchargé vers le répertoire de destination
                $newImageFile->move(
                    $this->getParameter('images_directory'), // Remplacez par le chemin approprié
                    $fileName
                );

                // Mettez à jour l'entité Topic avec le nouveau nom de fichier de l'image
                $topic->setImageFileName($fileName);
            }

            $entityManager->flush();

            return $this->redirectToRoute('app_topic_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('topic/edit.html.twig', [
            'topic' => $topic,
            'form' => $form->createView(),
        ]);
    }


    #[Route('/{id}', name: 'app_topic_delete', methods: ['POST'])]
    public function delete(Request $request, Topic $topic, EntityManagerInterface $entityManager): Response
    {
        /**
         * Supprime un topic.
         *
         * @param Request $request La requête HTTP
         * @param Topic $topic Le topic à supprimer
         * @param EntityManagerInterface $entityManager Le gestionnaire d'entités
         * @return Response La réponse HTTP
         */
        if ($this->isCsrfTokenValid('delete' . $topic->getId(), $request->request->get('_token'))) {
            $entityManager->remove($topic);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_topic_index', [], Response::HTTP_SEE_OTHER);
    }
}
