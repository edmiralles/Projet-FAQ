<?php

namespace App\Security\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class ReponseVoter extends Voter
{
    public const EDIT = 'REPONSE_EDIT';
    public const VIEW = 'REPONSE_VIEW';

    protected function supports(string $attribute, mixed $subject): bool
    {
        // replace with your own logic
        // https://symfony.com/doc/current/security/voters.html
        return in_array($attribute, [self::EDIT, self::VIEW])
            && $subject instanceof \App\Entity\Reponse;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }

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
                // logic to determine if the user can VIEW
                // return true or false
                return $subject -> getUser() === $user;
                break;
        }

        return false;
    }
}
