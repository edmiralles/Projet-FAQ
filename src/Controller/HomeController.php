<?php

namespace App\Controller;

use App\Entity\Question;
use App\Entity\Reponse;
use App\Entity\Vote;
use App\Form\QuestionType;
use App\Form\ReponseType;
use App\Repository\QuestionRepository;
use App\Repository\ReponseRepository;
use App\Repository\UserRepository;
use App\Service\UploadService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
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
                ->from(new Address('noreply@faq.com', 'FAQ'))
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

        //verifie si l'utilisateur connecté a deja voté pour une réponse pour cette question
        /*$user = $this->getUser();
        if ($user !== null){
            $hasVoted = $this->reponseRepository->hasVoted($user, $question);
        }*/

        return $this->render('home/question.html.twig', [
            'question' => $question,
            'formReponse' => $form,
            //'hasVoted' => $hasVoted ?? false //coalescence des nulls a gauche des ?? prioritaire mais si null, droite devient prioritaire
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

    #[IsGranted('REPONSE_DELETE', 'reponse', 'Vous ne pouvez pas supprimer une question qui ne vous appartient pas')]
    #[Route(path: '/delete/reponse{id}', name: 'app_delete_reponse', requirements: ['id' => '\d+'])]
    public function deleteReponse(Reponse $reponse, Request $request): RedirectResponse
    {
        //recuperation du jeton CRSF du formulaire
        $token = $request->request->get('_token');
        $method = $request->request->get('_method');
        $question = $reponse->getQuestion();

        if($method === 'DELETE' && $this->isCsrfTokenValid('delete_reponse-' . $reponse->getId(), $token)){

            $this->entityManager->remove($reponse);
            $this->entityManager->flush();
    
            $this->addFlash('success', 'votre réponse a bien été supprimée');
    
            return $this->redirectToRoute('app_question',[
                'id' => $question->getId()
            ]);  
        }else{
        $this->addFlash('error', "vous n'etes pas autorisé à effectuer cette action");
        }
    
        return $this->redirectToRoute('app_question',[
            'id' => $question->getId()
        ]);   
        }

    #[IsGranted('QUESTION_DELETE', 'question', 'Vous ne pouvez pas supprimer une question qui ne vous appartient pas')]
    #[Route(path: '/delete/question{id}', name: 'app_delete_question', requirements: ['id' => '\d+'])]
    public function deleteQuestion(Question $question, Request $request): RedirectResponse
    {
        //recuperation du jeton CRSF du formulaire
        $token = $request->request->get('_token');
        $method = $request->request->get('_method');

        if($method === 'DELETE' && $this->isCsrfTokenValid('delete_question', $token)){

            $this->entityManager->remove($question);
            $this->entityManager->flush();
    
            $this->addFlash('success', 'votre question a bien été supprimée');
    
            return $this->redirectToRoute('app_home');
        }
        $this->addFlash('error', "vous n'etes pas autorisé à effectuer cette action");
    
        return $this->redirectToRoute('app_question',[
            'id' => $question->getId()
        ]);   
        }

    #[IsGranted('QUESTION_UPDATE', 'question', 'Vous ne pouvez pas modifier une question qui ne vous appartient pas')]
    #[Route('/question{id}/update', name: 'app_question_update', requirements: ['id' => '\d+'])]
    public function updateQUestion(Request $request, Question $question): Response
    {

        
        $form = $this->createForm(QuestionType::class, $question);
        $form -> handleRequest($request);

            if($form->isSubmitted() && $form->isValid()){

                $this->entityManager->persist($question);
                $this->entityManager->flush();

                $this->addFlash('success', 'votre question a bien été mise à jour');

                return $this->redirectToRoute('app_question',[
                    'id' => $question->getId()
                ]);
            }
            return $this->render('question/updateQuestion.html.twig', [
                'formEditQUestion' => $form,
                'question' => $question,
            ]);
    }

    #[IsGranted('REPONSE_VOTE', 'reponse', 'Vous ne pouvez pas voter pour cette réponse')]
    #[Route('/reponse/vote{id}', name: 'app_reponse_vote'/*, methods:['POST']*/)]
    public function vote(Reponse $reponse, Request $request): RedirectResponse
    {
        $token = $request->request->get('_token');

        if($request->getMethod() === 'POST' && $this->isCsrfTokenValid('vote-' . $reponse->getId(), $token)){
            /** @var User $user */
            $user = $this->getUser();

            //associe la réponse à l'utilisateur
            $user->addVoter($reponse);
            //$reponse->addVote($user);

            $this->entityManager->persist($user);
            $this->entityManager->flush();

            $this->addFlash('success', 'merci pour votre vote !');
        }else{
            $this->addFlash('error', 'Vous ne pouvez plus voter ici');
        }

        return $this->redirectToRoute('app_question',[
            'id' => $reponse->getQuestion()->getId()
        ]);
    }
}
