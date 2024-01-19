<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;


#[IsGranted('ADMIN_KEY', null, 'Vous ne possédez pas les droits requis')]


class AdminController extends AbstractController
{
    public function __construct( 
        private EntityManagerInterface $entityManager
        )
    {
    }

    #[Route('/user/profil', name: 'app_admin')]
    public function index(UserRepository $userRepository): Response
    {
        $users = $userRepository -> findBy([], ['nom' => 'ASC']);

        return $this->render('admin/index.html.twig', [
            'users' => $users,
        ]);
    }

}
