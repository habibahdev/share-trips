<?php

namespace App\Form;

use App\Entity\TripStop;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\PositiveOrZero;

class TripStopType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('city', TextType::class, [
                'label' => false,
                'attr' => [
                    'placeholder' => 'Ville d\'arrêt',
                    'class' => 'login-input'
                ],
                'constraints' => [
                    new NotBlank(message: 'La ville est obligatoire.')
                ]
            ])
            ->add('extraPrice', NumberType::class, [
                'label' => false,
                'required' => false,
                'scale' => 2,
                'attr' => [
                    'placeholder' => 'Prix supplémentaire (€)',
                    'class' => 'login-input',
                    'min' => 0,
                    'step' => '0.50'
                ],
                'constraints' => [
                    new PositiveOrZero(message: 'Le prix doit être positif ou nul.')
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => TripStop::class
        ]);
    }
}
