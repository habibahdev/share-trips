<?php

namespace App\Form;

use App\Entity\Vehicle;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\Validator\Constraints\Regex;

class VehicleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('brand', TextType::class, [
                'label' => 'Marque',
                'constraints' => [
                    new Length(max: 50),
                    new NotBlank(message: 'La marque est obligatoire.')
                ]
            ])
            ->add('model', TextType::class, [
                'label' => 'Modèle',
                'constraints' => [
                    new Length(max: 50),
                    new NotBlank(message: 'Le modèle est obligatoire.')
                ]
            ])
            ->add('color', TextType::class, [
                'label' => 'Couleur',
                'required' => false,
                'constraints' => [
                    new Length(max: 30)
                ]
            ])
            ->add('plate', TextType::class, [
                'label' => 'Plaque d\'immatriculation',
                'constraints' => [
                    new Length(max: 20),
                    new NotBlank(message: 'La plaque d\'immatriculation est obligatoire.'),
                    new Regex(
                        pattern: '/^[A-Z]{2}-[0-9]{3}-[A-Z]{2}$/',
                        message: 'Format attendu: AB-123-CD'
                    )
                ]
            ])
            ->add('seats', IntegerType::class, [
                'label' => 'Nombre de place',
                'constraints' => [
                    new NotBlank(message: 'Le nombre de places disponibles est obligatoire.'),
                    new Range(
                        min: 1,
                        max: 8,
                        notInRangeMessage: 'Le nombre de places doit être compris entre {{ min }} et {{ max }}.'
                    )
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Vehicle::class,
        ]);
    }
}
