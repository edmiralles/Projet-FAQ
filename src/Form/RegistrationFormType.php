<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Unique;

class RegistrationFormType extends AbstractType
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
            ->add('plainPassword', PasswordType::class, [
                                // instead of being set onto the object directly,
                // this is read and encoded in the controller
                'mapped' => false,
                'help' => "Le mot de passe doit contenir 6 caractères au minimum",
                'attr' => ['autocomplete' => 'new-password'],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez entrer un mot de passe',
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Votre mot de passe doit comporter au moins {{ limit }} caractères',
                        // max length allowed by Symfony for security reasons
                        'max' => 4096,
                        'maxMessage'=>'Votre mot de passe ne doit pas dépasser les {{ limit }} caractères'
                    ]),
                ],
            ])

            ->add('avatarFile', FileType::class,[
                'mapped' => false,
                'required' => false,
                'label' => 'photo de profil',
                'help' => "Votre photo de profil ne doit pas dépasser les 1Mo et doit être un type : PNG, WEBP ou JPG",
                'constraints' => [
                    new File([
                        'extensions' => ['png', 'jpeg', 'jpg', 'webp'],
                        'extensionsMessage' =>'votre fichier n\'est pas une image accepté',
                        'maxSize' =>'1M',
                        'maxSizeMessage' => "Votre image est trop volumineuse, l'image ne dois pas dépasser {{ limit }}."
                    ]),
                ]
            ])
            ->add('agreeTerms', CheckboxType::class, [
                'label' => 'j\'accepte et j\autorise les termes d\'utilisations',
                'mapped' => false,
                'constraints' => [
                    new IsTrue([
                        'message' => 'Vous devez accepté les termes d\'utilisation.',
                    ]),
                ],
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
