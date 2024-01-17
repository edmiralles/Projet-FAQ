<?php

namespace App\Controller;

use App\Entity\Question;
use App\Entity\Reponse;
use App\Form\ReponseType;
use App\Repository\QuestionRepository;
use App\Repository\ReponseRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

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

        $logged = false;
         if ($this->getUser()) {
             $logged = true;
         }

        return $this->render('home/index.html.twig', [
            'questions' => $questions,
            'logged' => $logged
        ]);
    }

    //affichage de la question delectionné et de ses reponses
    #[Route('/question/{id}', name: 'app_question', requirements: ['id' => '\d+'])]
    public function detailedQuestion(Question $question, Request $request, MailerInterface $mailer): Response
    {
        $reponse = new Reponse();
        $form = $this->createForm(ReponseType::class, $reponse);
        //clone du formulaire vide dans un nouvel objet
        $emptyform = clone $form;
        $form -> handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $reponse -> setDateCreation(new \DateTime());
            $reponse -> setQuestion($question);
            $reponse -> setUser($this->getUser());

            $this->entityManager->persist($reponse);
            $this->entityManager->flush();

            $this->addFlash('success','votre message a bien été publié');

            //ne pas envoyer d'email si la réponse est postée par l'auteur de la question
            if($reponse->getUser() !== $question->getUser()){

            //envoyer un email
            $email = (new TemplatedEmail())
                ->from(new Address('noreply@gaq.com', 'FAQ'))
                ->to(new Address($question->getUser()->getEmail(), $question->getUser()->getNom()))
                ->subject('Bonne nouvelle !')
            
                // path of the Twig template to render
                ->htmlTemplate('emails/new_reponse.html.twig')

                // pass variables (name => value) to the template
                ->context([
                    'name' => $question->getUser()->getNom(),
                    'question' => $question->getTitre(),
                    'url' => $this->generateUrl(
                        'app_question',
                        ['id' => $question->getId()],
                        UrlGeneratorInterface::ABSOLUTE_URL
                        )
                ]);

                $mailer->send($email);

                //on reclone notre objet formulaire vide dans l'objet de départ
                $form = clone $emptyform;
            }
        
        }
        return $this->render('home/question.html.twig', [
            'question' => $question,
            'formReponse' => $form
        ]);
                             
    }

    //#[IsGranted('REPONSE_EDIT', 'reponse', 'vous ne pouvez pas éditer cette réponse')] autre option
    #[Route('/Response/{id}/edit', name: 'app_response_edit', requirements: ['id' => '\d+'])]
    public function newResponse(Reponse $reponse, Request $request): Response
    {
        if(!$this->isGranted('REPONSE_EDIT', $reponse)){
            throw $this->createAccessDeniedException("vous ne pouvez pas modifier cette réponse");
        }

        $form = $this->createForm(ReponseType::class, $reponse);
        $form -> handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){

            $reponse->setDateModification(new \DateTime());

            $this->entityManager->persist($reponse);
            $this->entityManager->flush();

            $this->addFlash('success', 'votre réponse a bien été modifié');

            return $this->redirectToRoute('app_question', [
                'id' => $reponse ->getQuestion()->getId()
            ]);
        }

        return $this -> render('home/editResponse.html.twig', [
            'formEditResponse' => $form,
            'reponse' => $reponse
        ]);
    }
}
