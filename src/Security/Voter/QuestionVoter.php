<?php

namespace App\Security\Voter;

use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class QuestionVoter extends Voter
{
    public const ADD = 'QUESTION_ADD';
    public const DELETE = 'QUESTION_DELETE';
    public const UPDATE = 'QUESTION_UPDATE';

    public function __construct(
        private Security $security
    ){

    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        //si l'attribut correspond à la valeur de la constante "ADD", on applique les regles du voter
        if($attribute == self::ADD){
            return true;
        }

        return in_array($attribute, [self::DELETE, self::UPDATE])
            && $subject instanceof \App\Entity\Question;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }

        if ($this->security->isGranted('ROLE_ADMIN')) {
            return true;
        }

        // ... (check conditions and return true to grant permission) ...
        switch ($attribute) {
            case self::ADD:
                return $this->security->isGranted('ROLE_USER');
                break;
            case self::DELETE:
            case self::UPDATE:
                return $subject->getUser() === $user || $this->security->isGranted('ROLE_ADMIN');
                break;
        }

        return false;
    }
}
