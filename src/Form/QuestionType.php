<?php

namespace App\Form;

use App\Entity\Question;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class QuestionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre', TextType:: class, [
                'label'=> 'Votre question en résumé',
                'help' => "Le résumé de votre question sera visible en tant que tite de votre sujet. Il doit contenir entre 3 et 150 caractères",
                'constraints' => [
                    new NotBlank([
                        'message' => 'La question est requise'
                    ]),
                    new Length([
                        'min' => 3,
                        'minMessage' => 'Le résumé de la question doit contenir au minimum {{ limit }} caractères',
                        'max' => 150,
                        'maxMessage' => 'Le résumé de la question doit contenir au maximum {{ limit }} caractères'
                    ])
                ]
            ])
            ->add('contenu', TextareaType::class, [
                'label'=> 'Votre question détaillée',
                'help' => "Vous pouvez ou non détailler votre question ici.",
                'attr' => ['rows' =>10],
                'required' => false,
                'constraints' => [
                    new Length([
                        'min' => 10,
                        'minMessage' => 'La question doit contenir au minimum {{ limit }} caractères'
                    ])
                ]
            ])
            ->add('submit', SubmitType::class, [
                'label' => "Poster"
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Question::class,
        ]);
    }
}
