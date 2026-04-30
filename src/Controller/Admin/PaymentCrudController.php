<?php

namespace App\Controller\Admin;

use App\Entity\Payment;
use App\Enum\PaymentStatus;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

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
            TextField::new('stripeSessionId', 'Session Stripe')
                ->setFormTypeOption('disabled', true)
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
