<?php

namespace App\Form;

use App\Entity\Trip;
use App\Entity\Vehicle;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\GreaterThan;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\PositiveOrZero;
use Symfony\Component\Validator\Constraints\Range;

class TripType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('origin', TextType::class, [
                'label' => 'Ville de départ',
                'constraints' => [
                    new NotBlank(message: 'La ville de départ est obligatoire.'),
                    new Length(max: 100)
                ]
            ])
            ->add('destination', TextType::class, [
                'label' => 'Ville d\'arrivée',
                'constraints' => [
                    new NotBlank(message: 'La ville d\'arrivée est obligatoire.'),
                    new Length(max: 100)
                ]
            ])
            ->add('departureAt', DateTimeType::class, [
                'label' => 'Date et heure du départ',
                'input' => 'datetime_immutable',
                'format' => "yyyy-MM-dd'T'HH:mm",
                'html5' => false,
                'widget' => 'choice',
                'constraints' => [
                    new NotBlank(message: 'La date de départ est obligatoire.'),
                    new GreaterThan(
                        value: 'now',
                        message: 'La date de départ doit être plus tard qu\'aujourd\'hui.'
                    )
                ]
            ])
            ->add('availableSeats', IntegerType::class, [
                'label' => 'Nombre de plases disponibles',
                'constraints' => [
                    new NotBlank(message: 'Le nombre de places est obligatoire.'),
                    new Range(
                        min: 1,
                        max: 8,
                        notInRangeMessage: 'Le nombre de places doit être compris entre {{ min }} et {{ max }}.'
                    )
                ]
            ])
            ->add('pricePerSeat', MoneyType::class, [
                'label' => 'Prix par place',
                'currency' => 'EUR',
                'constraints' => [
                    new NotBlank(message: 'Le prix est obligatoire.'),
                    new PositiveOrZero(message: 'Le prix doit être positif ou nul.')
                ]
            ])
            ->add('vehicle', EntityType::class, [
                'class' => Vehicle::class,
                'choices' => $options['vehicles'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Trip::class,
            'vehicles' => null
        ]);
    }
}
