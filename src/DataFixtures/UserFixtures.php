<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

;

class UserFixtures extends Fixture
{
    public function __construct(private UserPasswordHasherInterface $passwordHasher)
    {
        
    }


    public function load(ObjectManager $manager): void
    {

        $faker = Faker\Factory::create();

        // creation de 50 utilisateurs
        for($i = 0; $i < 50; $i++){
            $user = new User();
            $user -> setPassword($this->passwordHasher->hashPassword($user, 'secret'));
            $user -> setEmail($faker->unique()->email);
            $user -> setNom($faker->name);
            $user -> setIsVerified($faker->boolean);
            //persiste les données
            $manager -> persist($user);

            $this->addReference('user'. $i, $user);
        }

        //met à jour les modifications en BDD
        $manager->flush();
    }
}
