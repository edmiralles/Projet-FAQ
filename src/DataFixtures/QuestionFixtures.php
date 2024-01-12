<?php

namespace App\DataFixtures;

use App\Entity\Question;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker;
;

class QuestionFixtures extends Fixture implements DependentFixtureInterface
{
    public function getDependencies(): array
    {
        //Il faut que les fixtures user soient generees avant de generer les questions
        return[
            UserFixtures::class
        ];
    }


    public function load(ObjectManager $manager) : void
    {

        $faker = Faker\Factory::create();

        // creation de 200 questions
        for($i = 0; $i < 200; $i++){
            $number = $faker->numberBetween(0,49);
            $user = $this->getReference("user$number");

            $question = new Question();
            $question -> setTitre($faker->sentence(2));
            $question -> setContenu("{$faker->sentence(5)} ?");
            $question -> setDateCreation($faker->dateTimeBetween('-8 years', '-1 week'));
            $question -> setUser($user);

            //persiste les données
            $manager -> persist($question);

            $this->addReference('question'. $i, $question);
        }

        $manager->flush();
    }
}
