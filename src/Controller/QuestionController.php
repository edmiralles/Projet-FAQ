<?php

namespace App\Controller;

use App\Entity\Question;
use App\Form\QuestionType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class QuestionController extends AbstractController
{

    public function __construct( 
        private EntityManagerInterface $entityManager
        )
    {
    }

    //au minimum tu dois avoir le role user donc etre connecté
    #[IsGranted('QUESTION_ADD', null, "ouvrez un compte ou connectez vous pour poser une question")]
    #[Route('/new/question', name: 'app_new_question')]
    public function index(Request $request/*, Security $security*/): Response
    {
        /*if(!$security->isGranted('ROLE_USER')){
            return*/

        $question = new Question();
        $form = $this->createForm(QuestionType::class, $question);

        $form -> handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $question -> setDateCreation(new \DateTime());
            $question -> setUser($this->getUser());

            $this->entityManager->persist($question);
            $this->entityManager->flush();

            $this->addflash('success', 'votre question a bien été publié');

            return $this->redirectToRoute('app_question', [
                'id' => $question->getId()
            ]);
        }

        return $this->render('question/index.html.twig', [
            'questionForm' => $form,
        ]);
    }
}
