<?php

namespace App\Service;

use App\Entity\User;
use App\Entity\Roles; // Assurez-vous d'importer la classe Roles si elle n'est pas déjà importée
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserRegistrationService
{
    private $entityManager;
    private $userPasswordHasher;

    public function __construct(EntityManagerInterface $entityManager, UserPasswordHasherInterface $userPasswordHasher)
    {
        $this->entityManager = $entityManager;
        $this->userPasswordHasher = $userPasswordHasher;
    }

    public function registerUser(User $user, string $plainPassword): void
    {
        // Hachage et configuration du mot de passe de l'utilisateur
        $user->setPassword(
            $this->userPasswordHasher->hashPassword(
                $user,
                $plainPassword
            )
        );

        // Extraction de l'adresse e-mail de l'utilisateur
        $email = $user->getEmail();
        $validDomains = ['external', 'insider', 'collaborator'];

        // Séparation de l'adresse e-mail en parties
        $mailParts = explode('@', $email);

        // Vérification si l'adresse e-mail a deux parties (avant et après @)
        if (count($mailParts) === 2) {
            // Extraction du domaine de l'adresse e-mail
            $domain = explode('.', $mailParts[1])[0];

            // Vérification si le domaine est valide
            if (!in_array($domain, $validDomains)) {
                throw new \InvalidArgumentException('Adresse e-mail invalide');
            }

            // Recherche du rôle correspondant au domaine dans la base de données
            $role = $this->entityManager->getRepository(Roles::class)->findOneBy(['role_name' => $domain]);
            if ($role) {
                // Assignation du rôle à l'utilisateur
                $user->setRoles($role);
            }
        } else {
            throw new \InvalidArgumentException('Adresse e-mail invalide');
        }

        // Définir la date de création de l'utilisateur
        $user->setCreatedAt(new \DateTimeImmutable());

        // Persister l'utilisateur dans la base de données
        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }
}
