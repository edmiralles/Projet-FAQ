<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ProfilypeType;
use App\Service\UploadService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{

    public function __construct( 
        private EntityManagerInterface $entityManager
        )
    {
    }

    #[Route('/user/profile{id}', name: 'app_user_profile')]
    public function index(User $user): Response
    {
            return $this->render('user/index.html.twig', [
                'user' => $user,
            ]);
    }

    #[Route('/user/profile{id}/update', name: 'app_user_profile_update')]
    public function updateProfil(Request $request, UploadService $uploadService): Response
    {
        /** @var User $user */
        $user =$this->getUser();

        $form = $this->createForm(ProfilypeType::class, $user/*,[
            'is_profile => true
        ]si j'avais utiliser le meme form*/);
        $form -> handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){

            $avatarFile = $form->get('avatarFile')->getData();

            //si une image a été soumise, on la traite
            if($avatarFile){
                $fileName = $uploadService->upload($avatarFile, $user->getAvatar());
                $user->setAvatar($fileName);
            }

            $this->entityManager->persist($user);
            $this->entityManager->flush();

            $this->addFlash('success', 'votre profil a bien été actualisé');

            return $this->redirectToRoute('app_user_profile', [
                'id' => $user->getId()
            ]);
        }
            return $this->render('user/updateProfil.html.twig', [
                'formEditProfil' => $form,
                'user' => $user,
            ]);
    }
}


