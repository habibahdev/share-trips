<?php

namespace App\Controller\Admin;

use App\Entity\Payment;
use App\Enum\PaymentMethod;
use App\Enum\PaymentStatus;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;

/**
 * @extends AbstractCrudController<Payment>
 */
class PaymentCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Payment::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInPlural('Paiements')
            ->setEntityLabelInSingular('Paiement')
            ->setDefaultSort(['id' => 'DESC'])
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        $disabled = $pageName === Crud::PAGE_EDIT;
        return [
            AssociationField::new('payer', 'Payeur')
                ->setFormTypeOption('disabled', $disabled),
            AssociationField::new('booking', 'Réservation')
                ->setFormTypeOption('disabled', $disabled),
            ChoiceField::new('method', 'Méthode de paiement')
                ->setChoices(
                    array_combine(
                        array_map(
                            fn(PaymentMethod $m) => $m->label(),
                            PaymentMethod::cases()
                        ),
                        PaymentMethod::cases()
                    )
                )
                ->setFormTypeOption('disabled', $disabled)
                ->hideOnIndex(),
            ChoiceField::new('status', 'Statut')
                ->setChoices([
                    'En attente' => PaymentStatus::Pending,
                    'Complété' => PaymentStatus::Completed,
                    'Échoué' => PaymentStatus::Failed,
                    'Remboursé' => PaymentStatus::Refunded
                ])
                ->renderAsBadges([
                    PaymentStatus::Pending->value => 'warning',
                    PaymentStatus::Completed->value => 'success',
                    PaymentStatus::Failed->value => 'danger',
                    PaymentStatus::Refunded->value => 'info'
                ]),
            DateTimeField::new('createdAt', 'Payé le')->hideOnForm(),
        ];
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->remove(Crud::PAGE_INDEX, Action::NEW)
            ->remove(Crud::PAGE_INDEX, Action::DELETE)
            ->setPermission(Action::DELETE, 'ROLE_ADMIN')
        ;
    }
}
