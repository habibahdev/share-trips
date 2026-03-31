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
        $user = new User();
        $user->setEmail('contact@sharetrips.fr')
             ->setFirstName('Emmanuelle')
             ->setLastName('Dubernard')
             ->setIsVerified(true)
             ->setRoles(['ROLE_ADMIN'])
             ->setStatus(UserStatus::Active)
             ->setPassword($this->hasher->hashPassword($user, 'admin23'));
        $manager->persist($user);
        $manager->flush();
    }
}
