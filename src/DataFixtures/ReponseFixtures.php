<?php

namespace App\DataFixtures;

use App\Entity\Reponse;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker;
;

class ReponseFixtures extends Fixture implements DependentFixtureInterface
{
    public function getDependencies(): array
    {
        //Il faut que les fixtures user soient generees avant de generer les questions
        return[
            UserFixtures::class,
            QuestionFixtures::class
        ];
    }


    public function load(ObjectManager $manager) : void
    {

        $faker = Faker\Factory::create();

        // creation de 200 questions
        for($i = 0; $i < 1000; $i++){
            //recuperation d'utilisateurs aleatoires
            $user = $this->getReference("user{$faker->numberBetween(0, 49)}");
            //recuperation d'une question aleatoire
            $question = $this->getReference("question{$faker->numberBetween(0, 199)}");

            $dateCreationQuestion = $question->getDateCreation()->format('Y-m-d H:i:s');

            $reponse = new Reponse();
            $reponse -> setContenu($faker->realText);
            $reponse -> setDateCreation($faker->dateTimeBetween($dateCreationQuestion));
            $reponse -> setUser($user);
            $reponse -> setQuestion($question);

            for($j=0; $j < $faker->numberBetween(0, 15); $j++){
                //recupere un utilisateur de maniere aleatoire
                $user = $this->getReference("user{$faker->numberBetween(0, 49)}");
                //ajoute l'utilisateur à la collection
                $reponse->addVoter($user);
            }

            //persiste les données
            $manager -> persist($reponse);

        }

        $manager->flush();
    }
}
