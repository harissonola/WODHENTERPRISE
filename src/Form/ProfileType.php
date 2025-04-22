<?php

namespace App\Form;

use App\Entity\Formation;
use App\Entity\Premium;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProfileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email')
            ->add('fname')
            ->add('lname')
            ->add('image',FileType::class,[
                'label' => false,
                'data_class' => null,
                'attr' => [
                    'accept' => "image/png, image/jpeg",
                    'hidden' => 'hidden',
                    'class' => 'account-file-input'
                ],
            ])
            ->add('pseudo')
            ->add('number')
            ->add('facebook')
            ->add('tiktok')
            ->add('twitter')
            ->add('instagram')
            ->add('linkedin')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
