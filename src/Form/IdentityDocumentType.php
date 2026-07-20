<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;

class IdentityDocumentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('identityDocument', FileType::class, [
                'label' => 'Permis de conduire',
                'mapped' => false,
                'required' => true,
                'help' => 'Formats acceptés: JPEG, JPG, PNG, WEBP, PDF. Taille max : 5 Mo.',
                'constraints' => [
                    new NotBlank(message: 'Veuillez fournir une pièce.'),
                    new File(
                        maxSize:'5M',
                        mimeTypes: [
                            'image/jpeg',
                            'image/png',
                            'image/webp',
                            'application/pdf'
                        ],
                        mimeTypesMessage: 'Format non autorisé. Formats accpetés : JPG, PNG, WEBP, PDF'
                    )
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([]);
    }
}
