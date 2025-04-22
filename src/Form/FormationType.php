<?php

namespace App\Form;

use App\Entity\Formation;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FormationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name')
            ->add('slug',TextType::class,[
                'required' => false,
            ])
            ->add('image',FileType::class,[
                'mapped' => false,
                'required' => false,
            ])
            ->add('description',TextareaType::class,[
                'attr' => [
                    'style' => 'resize: none;',
                    'rows' => 6,
                ]
            ])
            ->add('descriptionFull',TextareaType::class,[
                'attr' => [
                    'style' => 'resize: none;',
                    'rows' => 6,
                ]
            ])
            ->add('deboche',TextareaType::class,[
                'attr' => [
                    'style' => 'resize: none;',
                    'rows' => 6,
                ]
            ])
            ->add('motif',TextareaType::class,[
                'attr' => [
                    'style' => 'resize: none;',
                    'rows' => 6,
                ]
            ])
            ->add('premium')
            ->add('linkName',TextType::class,[
                'mapped' => false,
                'required' => false,
                'label' => false,
                'attr' => [
                    'placeholder' => 'Example',
                ]
            ])
            ->add('link',UrlType::class,[
                'mapped' => false,
                'required' => false,
                'label' => false,
                'attr' => [
                    'value' => 'https://',
                    'placeholder' => 'https://example.com',
                ]

            ])
            ->add('save',SubmitType::class,[
                'label' => 'Enregistrer',
                'attr' => [
                    'class' => 'btn btn-primary'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Formation::class,
        ]);
    }
}
