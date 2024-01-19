<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ProfilypeType;
use App\Service\UploadService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('USER_ACCESS', null, 'veuillez vous connecter pour accéder à cette partie')]
class UserController extends AbstractController
{

    public function __construct( 
        private EntityManagerInterface $entityManager
        )
    {
    }

    #[Route('/user/profile{id}', name: 'app_user_profile')]
    public function index(): Response
    {
        /** @var User $user */
        $user =$this->getUser();

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

    #[Route(path: '/delete/profil{id?}', name: 'app_delete_profil')]
    public function deleteProfil(?User $user, Request $request): Response
    {
        //recuperation du jeton CRSF du formulaire
        $token = $request->request->get('_token');
        $method = $request->request->get('_method');

        //private function isValidCSRFToken qui reprend ce code si je voulais le découper
        // verifier le jeton crsf selon si l'on est un admin ou pas
        if($user !== null && $this->isGranted('ROLE_ADMIN')){
            $isTokenValid = $this->isCsrfTokenValid('delete_user-'. $user->getId(), $token);
        }else{
            $isTokenValid = $this->isCsrfTokenValid('delete_user', $token);
        }
        if($method === 'DELETE' && $isTokenValid/*$this->isCsrfTokenValid('delete_user', $token)version sans admin*//*isValidCSRFToken($user, $token) si j'avais coupé ma methode je la regintre avec ma nouvelle private methode qui reprend le code*/){
        /** @var User $user */
        $user = $user ?? $this->getUser();
        $userConnected = $this->getUser();

        //supprime dans la bdd
        $this->entityManager->remove($user);
        $this->entityManager->flush();

        //supprimer l'avatar de l'utilisateur
        $filesystem = new Filesystem();
        if($user->getAvatar() !== 'imgs/user_default.jpg'){
            $filesystem->remove($user->getAvatar());
        }

        if($user !== $userConnected && $this->isGranted('ROLE_ADMIN')){
            $this->addFlash('success', 'Le compte utilisateur est désormais supprimé');

            return $this->redirectToRoute('app_admin');
        }
        //invalidation de la session utilisateur
        $session = $request->getSession();
        $session->invalidate();

        //annule le token de sécurité utilisateur qui etait lié à la session de connexion
        $this->container->get('security.token_storage')->setToken(null);


        $this->addFlash('success', 'votre compte est désormais supprimé');

        return $this->redirectToRoute('app_home');
        }
        //retour vers la page de profil si le token CRSF est invalide
        $this->addFlash('error', 'JETON crsf invalide');
        return $this->redirectToRoute('app_home');
    }


    /*private function isValidCSRFToken(?User $user, string $token):bool
    {
        if($user !== null && $this->isGranted('ROLE_ADMIN')){
            $isTokenValid = $this->isCsrfTokenValid('delete_user-'. $user->getId(), $token);
        }else{
            $isTokenValid = $this->isCsrfTokenValid('delete_user', $token);
        }

        return $this->isCsrfTokenValid('delete_user', $token);
    }*/
}


