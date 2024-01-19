<?php

namespace App\Controller;

use App\Entity\Question;
use App\Form\QuestionType;
use App\Repository\QuestionRepository;
use App\Repository\ReponseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

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

    #[IsGranted('USER_ACCESS', null, "ouvrez un compte ou connectez vous pour pouvoir effectuer cette action")]
    #[Route('/{type}/report/{id}', name: 'app_report', requirements: ['id' => '\d+', 'type' => 'question|reponse'])]
    public function report(int $id, string $type, MailerInterface $mailer, Request $request, QuestionRepository $questionRepository, ReponseRepository $reponseRepository):RedirectResponse
    {
        if($type === 'question'){
            $question = $questionRepository->find($id);
            if(!$question){
                throw $this->createNotFoundException('Aucune question sous cet ID');
            }
            $questionId = $question->getId();
        } else{
            $reponse = $reponseRepository->find($id);
            if(!$reponse){
                throw $this->createNotFoundException('Aucune réponse sous cet ID');
            }
            $questionId = $reponse->getQUestion()->getId();
        }


        $token = $request->request->get('_token');

        if($this->isCsrfTokenValid("report-$type-$id", $token)){
        /** @var User $user */
        $user = $this->getUser();

        $email = (new TemplatedEmail())
                ->from(new Address($user->getEmail(), $user->getNom()))
                ->to(new Address('report@faq.com', 'FAQ'))
                ->subject('Signalement FAQ')
            
                // path of the Twig template to render
                ->htmlTemplate('emails/signalement.html.twig')

                // pass variables (name => value) to the template
                ->context([
                    'type' => $type,
                    'nom' => $user->getNom(),
                    'url' => $this->generateUrl(
                        'app_question',
                        ['id' => $questionId],
                        UrlGeneratorInterface::ABSOLUTE_URL
                        )
                ]);

                $mailer->send($email);
        
        $this->addFlash('success', 'Votre signalement a bien été transmis!');
        }else{
            $this->addFlash('error', 'Jeton CRSF invalide');
        }

        return $this->redirectToRoute('app_question',[
            'id'=> $questionId
        ]);
    }
}
