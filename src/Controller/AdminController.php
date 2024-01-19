<?php

namespace App\Controller;

use App\Entity\Question;
use App\Entity\Reponse;
use App\Entity\User;
use App\Repository\QuestionRepository;
use App\Repository\ReponseRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;


#[IsGranted('ADMIN_KEY', null, 'Vous ne possédez pas les droits requis')]
#[Route('/admin', name: 'app_admin')]

class AdminController extends AbstractController
{
    public function __construct( 
        private EntityManagerInterface $entityManager
        )
    {
    }

    #[Route('', name: '')]
    public function index(UserRepository $userRepository): Response
    {
        $users = $userRepository -> findBy([], ['nom' => 'ASC']);

        return $this->render('admin/index.html.twig', [
            'users' => $users,
        ]);
    }

    #[Route('/user/{id}/role', name: '_change_role')]
    public function roleAdmin(User $user):RedirectResponse
    {
        $user->setRoles(['ROLE_ADMIN']);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $this->addFlash('success', "L'utilisateur {$user->getNom()} est maintenant un administrateur");

        return $this->RedirectToRoute('app_admin');
    }

    #[Route('/analysis', name: '_analysis')]
    public function questionAnalysis( QuestionRepository $questionRepository): Response
    {
        $question = $questionRepository->countFamousQuestion();

        return $this->render('admin/analysis.html.twig', [
            'questions' => $question
        ]);
    }

    #[Route('/voter', name: '_voter')]
    public function reponseAnalysis( ReponseRepository $reponseRepository): Response
    {
        $reponse = $reponseRepository->getFamousReponse();
        

        return $this->render('admin/voter.html.twig', [
            'reponses' => $reponse
        ]);
    }
}
