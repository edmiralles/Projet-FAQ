<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class ProfilypeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom d\'utilisateur',
                'required' => false, //desactivation de l'attribut html 5 required
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez saisir un nom d\'utilisateur'
                    ]), //le champ ne doit pas etre vide
                    new Length([
                        'min' => 3,
                        'minMessage' => 'Votre nom d\'utilisateur doit comporter au moins {{ limit }} caractères',
                        'max' => 50,
                        'maxMessage'=>'Votre nom d\'utilisateur ne doit pas dépasser les {{ limit }} caractères'
                    ])
                    ],
            ])
            ->add('email', EmailType::class, [
                'required' => false,
                'constraints' => [
                    new NotBlank([
                        'message' => "l'adresse email est requise"
                    ]),
                    new Email([
                        'message' => "l'adresse emeil est invalide"
                    ])
                ]
            ])
            ->add('avatarFile', FileType::class,[
                'mapped' => false,
                'required' => false,
                'label' => 'photo de profil',
                'help' => "Votre photo de profil ne doit pas dépasser les 1Mo et doit être un type : PNG, WEBP ou JPG",
                'constraints' => [
                    new File([
                        'extensions' => ['png', 'jpeg', 'jpg', 'webp'],
                        'extensionsMessage' =>'votre fichier n\'est pas une image acceptée',
                        'maxSize' =>'1M',
                        'maxSizeMessage' => "Votre image est trop volumineuse, l'image ne dois pas dépasser {{ limit }}."
                    ]),
                ]
            ])
            ->add('submit', SubmitType::class,[
                'label' => 'Valider'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
