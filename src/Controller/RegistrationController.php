<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Roles;
use App\Form\RegistrationFormType;
use App\Security\LoginFormAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, UserAuthenticatorInterface $userAuthenticator, LoginFormAuthenticator $authenticator, EntityManagerInterface $entityManager): Response
    {
        /**
         * Gère l'inscription d'un nouvel utilisateur.
         *
         * @param Request $request La requête HTTP
         * @param UserPasswordHasherInterface $userPasswordHasher L'interface pour hacher les mots de passe
         * @param UserAuthenticatorInterface $userAuthenticator L'interface pour l'authentification utilisateur
         * @param LoginFormAuthenticator $authenticator L'authentificateur de formulaire de connexion
         * @param EntityManagerInterface $entityManager Le gestionnaire d'entités
         * @return Response La réponse HTTP
         */

        // Création d'une nouvelle instance de l'entité User
        $user = new User();

        // Création du formulaire d'inscription en utilisant RegistrationFormType
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        // Vérification si le formulaire a été soumis et est valide
        if ($form->isSubmitted() && $form->isValid()) {
            // Hachage et configuration du mot de passe de l'utilisateur
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            // Extraction de l'adresse e-mail de l'utilisateur
            $email = $user->getEmail();
            $validDomains = ['external', 'insider', 'collaborator', 'admin'];

            // Séparation de l'adresse e-mail en parties
            $mailParts = explode('@', $email);

            // Vérification si l'adresse e-mail a deux parties (avant et après @)
            if (count($mailParts) === 2) {
                // Extraction du domaine de l'adresse e-mail
                $domain = explode('.', $mailParts[1])[0];

                // Vérification si le domaine est valide
                if (!in_array($domain, $validDomains)) {
                    // Ajout d'une erreur au formulaire en cas de domaine invalide
                    $form->addError(new FormError('Adresse e-mail invalide'));
                    return $this->render('registration/register.html.twig', [
                        'registrationForm' => $form->createView(),
                    ]);
                }

                // Recherche du rôle correspondant au domaine dans la base de données
                $role = $entityManager->getRepository(Roles::class)->findOneBy(['role_name' => $domain]);
                if ($role) {
                    // Assignation du rôle à l'utilisateur
                    $user->setRoles($role);
                }
            } else {
                // L'adresse e-mail n'est pas valide, ajouter une erreur au formulaire
                $form->addError(new FormError('Adresse e-mail invalide'));
                return $this->render('registration/register.html.twig', [
                    'registrationForm' => $form->createView(),
                ]);
            }

            // Définir la date de création de l'utilisateur
            $user->setCreatedAt(new \DateTimeImmutable());

            // Persister l'utilisateur dans la base de données
            $entityManager->persist($user);
            $entityManager->flush();

            // Authentification de l'utilisateur nouvellement enregistré
            return $userAuthenticator->authenticateUser(
                $user,
                $authenticator,
                $request
            );
        }

        // Afficher le formulaire d'inscription en cas d'erreurs ou de soumission invalide
        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }
}
