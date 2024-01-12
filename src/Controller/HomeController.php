<?php

namespace App\Controller;

use App\Entity\Question;
use App\Repository\QuestionRepository;
use App\Repository\ReponseRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    public function __construct( 
        private UserRepository $userRepository,
        private EntityManagerInterface $entityManager,
        private ReponseRepository $reponseRepository,
        private QuestionRepository $questionRepository
        )
    {
    }
    //acceuil: affichage de toutes les questions utilisateurs
    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        $questions = $this -> questionRepository -> findBy([],['dateCreation' => 'DESC']);

        return $this->render('home/index.html.twig', [
            'questions' => $questions
        ]);
    }

    //affichage de la question delectionné et de ses reponses
    #[Route('/question/{id}', name: 'app_question', requirements: ['id' => '\d+'])]
    public function detailedQuestion(Question $question): Response
    {

        return $this->render('home/question.html.twig', [
            'question' => $question,
        ]);
    }
}
