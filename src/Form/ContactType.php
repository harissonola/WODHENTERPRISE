<?php

namespace App\Form;

use App\DTO\ContactDTO;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContactType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class,[
                'empty_data' => '',
                'label' => false,
                'attr' => [
                    'placeholder' => "John Deo",
                ],
            ])
            ->add('email',EmailType::class,[
                'empty_data' => '',
                'label' => false,
                'attr' => [
                    'placeholder' => "john.deo@example.com",
                ],
            ])
            ->add('subject', TextType::class,[
                'empty_data' => '',
                'label' => false,
                'attr' => [
                    'placeholder' => "Demande de contact",
                ],
            ])
            ->add('message',TextareaType::class,[
                'empty_data' => '',
                'label' => false,
                'attr' => [
                    'style' => 'resize: none;',
                    'rows' => 4,
                    'placeholder' => "Entrez votre message",
                ]
            ])
            ->add('save',SubmitType::class,[
                'label' => 'Envoyer',
                'attr' => [
                    'class' => 'btn btn-primary'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ContactDTO::class,
        ]);
    }
}
