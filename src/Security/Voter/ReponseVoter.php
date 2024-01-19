<?php

namespace App\Security\Voter;

use App\Repository\ReponseRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class ReponseVoter extends Voter
{
    public const EDIT = 'REPONSE_EDIT';
    public const VIEW = 'REPONSE_VIEW';
    public const DELETE = 'REPONSE_DELETE';
    public const VOTE = 'REPONSE_VOTE';

    public function __construct(
        private ReponseRepository $reponseRepository,
        private Security $security
        ){

    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        // replace with your own logic
        // https://symfony.com/doc/current/security/voters.html
        return in_array($attribute, [self::EDIT, self::VIEW, self::DELETE, self::VOTE])
            && $subject instanceof \App\Entity\Reponse;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }

        /*if ($this->security->isGranted('ROLE_ADMIN')) {
            return true;
        }*/

        // ... (check conditions and return true to grant permission) ...
        switch ($attribute) {
            case self::EDIT:
                // logic to determine if the user can EDIT
                // return true or false
                //si l'auteur du sujet(donc la reponse) est egal à l'utilisateur connecté
                //alors on autorise la modification de la réponse
                return $subject -> getUser() === $user;
                break;
            case self::VIEW:
                return $subject -> getUser() === $user;
                break;
            case self::DELETE:
                return $subject -> getUser() === $user|| $this->security->isGranted('ROLE_ADMIN');
                break;
            case self::VOTE:
                $hasVoted = $this->reponseRepository->hasVoted($user, $subject->getQuestion());
                return $hasVoted === false && $subject->getUser() !== $user;
                break;
        }

        return false;
    }
}
