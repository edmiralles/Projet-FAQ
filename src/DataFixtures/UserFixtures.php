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

        //creation d'un administrateur de test
        $admin = new User();
        $admin->setPassword($this->passwordHasher->hashPassword($user,'secret'));
        $admin->setNom('Leon LeLion');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setEMail('LeonLeLion@gmail.com');
        $admin->setISVerified(true);

        $manager->persist($admin);
        //met à jour les modifications en BDD
        $manager->flush();
    }
}
