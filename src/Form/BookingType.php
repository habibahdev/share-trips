<?php

namespace App\Form;

use App\Entity\Booking;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Range;

class BookingType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $availableSeats = $options['available_seats'];

        $builder
            ->add('seatsBooked', IntegerType::class, [
                'label' => 'Nombre de places',
                'attr' => [
                    'max' => $availableSeats,
                ],
                'constraints' => [
                    new NotBlank(message: 'Le nombre de places est obligatoire.'),
                    new Range(
                        min: 1,
                        max: $availableSeats,
                        notInRangeMessage: 'Vous pouvez réserver au plus {{ max }} place(s).'
                    ),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Booking::class,
        ]);

        $resolver->setRequired('available_seats');
        $resolver->setAllowedTypes('available_seats', 'int');
    }
}
