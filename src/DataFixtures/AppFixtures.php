<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Enum\UserStatus;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(private UserPasswordHasherInterface $hasher)
    {
    }

    public function load(ObjectManager $manager): void
    {
        $admin = new User();
        $admin->setEmail('contact@sharetrips.fr')
             ->setFirstName('Emmanuelle')
             ->setLastName('Dubernard')
             ->setIsVerified(true)
             ->setRoles(['ROLE_ADMIN'])
             ->setStatus(UserStatus::Active)
             ->setPassword($this->hasher->hashPassword($admin, 'admin23'));
        $manager->persist($admin);

        for ($i = 1; $i <= 5; $i++) {
            $user = new User();
            $user->setEmail(sprintf("usermail_%d@mail.fr", $i))
                 ->setFirstName(sprintf("userprenom_%d", $i))
                 ->setLastName(sprintf("usernom_%d", $i))
                 ->setIsVerified(true)
                 ->setRoles(['ROLE_USER'])
                 ->setStatus(UserStatus::Active)
                 ->setPassword($this->hasher->hashPassword($user, 'password'));
            $manager->persist($user);
        }
        $manager->flush();
    }
}
