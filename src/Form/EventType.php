<?php

namespace App\Form;

use App\Entity\Event;
use App\Entity\Formation;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EventType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name')
            ->add('slug', TextType::class, [
                'required' => false,
            ])
            ->add('price', IntegerType::class, [
                'attr' => [
                    'min' => 1,
                ],
                
            ])
            ->add('description', TextareaType::class, [
                'attr' => [
                    'style' => 'resize: none;',
                    'rows' => 6,
                ]
            ])
            ->add('image', FileType::class, [
                'mapped' => false,
                'required' => false,
                'attr' => [
                    'accept' => '.jpg, .gif, .png, .webp, .ico, .jpeg'
                ],
                'data_class' => null
            ])

            ->add('date', null, [
                'widget' => 'single_text'
            ])

            ->add('type', ChoiceType::class, [
                'choices' => [
                    'Promo' => 'promo',
                    'Presenciel' => 'presenciel',
                ]
            ])
            ->add('formations', EntityType::class, [
                'class' => Formation::class,
                'required' => false,
                'choice_label' => 'name',
                'multiple' => true,
                'attr' => [
                    'class' => 'select2',
                ]
            ])
            ->add('save', SubmitType::class, [
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
            'data_class' => Event::class,
        ]);
    }
}
