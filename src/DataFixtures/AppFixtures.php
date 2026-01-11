<?php

namespace App\DataFixtures;

use App\Entity\Livre;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        // Admin
        $admin = new User();
        $admin->setEmail('admin@library.com');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setNom('Admin');
        $admin->setPrenom('Super');
        $admin->setPassword($this->passwordHasher->hashPassword($admin, 'admin123'));
        $manager->persist($admin);

        // User
        $user = new User();
        $user->setEmail('user@library.com');
        $user->setRoles(['ROLE_USER']);
        $user->setNom('User');
        $user->setPrenom('Normal');
        $user->setPassword($this->passwordHasher->hashPassword($user, 'user123'));
        $manager->persist($user);

        // Books
        $booksData = [
            ['1984', 'George Orwell', '9780451524935', 5],
            ['Le Petit Prince', 'Antoine de Saint-Exupéry', '9780156012195', 3],
            ['Harry Potter à l\'école des sorciers', 'J.K. Rowling', '9782070584628', 1], // Just one copy
            ['Clean Code', 'Robert C. Martin', '9780132350884', 10],
            ['Dune', 'Frank Herbert', '9780441172719', 0], // Out of stock
        ];

        foreach ($booksData as $data) {
            $book = new Livre();
            $book->setTitre($data[0]);
            $book->setAuteur($data[1]);
            $book->setIsbn($data[2]);
            $book->setDescription('Description standard pour ' . $data[0]);
            $book->setNombreExemplaires($data[3]);
            $manager->persist($book);
        }

        $manager->flush();
    }
}
