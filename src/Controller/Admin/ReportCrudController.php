<?php

namespace App\Controller\Admin;

use App\Entity\Report;
use App\Enum\ReportStatus;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;

/**
 * @extends AbstractCrudController<Report>
 */
class ReportCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Report::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInPlural('Signalements')
            ->setEntityLabelInSingular('Signalement')
            ->setDefaultSort(['createdAt' => 'DESC'])
            ->showEntityActionsInlined()
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        $disabled = $pageName === Crud::PAGE_EDIT;

        return [
            IdField::new('id')->onlyOnIndex(),
            AssociationField::new('reporter', 'Signalé par')
                ->setFormTypeOption('disabled', $disabled),
            AssociationField::new('reported', 'Utilisateur signalé')
                ->setFormTypeOption('disabled', $disabled),
            AssociationField::new('booking', 'Réservation concernée')
                ->hideOnIndex()
                ->setFormTypeOption('disabled', $disabled),
            TextareaField::new('details', 'Détails')
                ->hideOnIndex()
                ->setFormTypeOption('disabled', $disabled),
            TextareaField::new('reason', 'Motif')
                ->setFormTypeOption('disabled', $disabled),
            ChoiceField::new('status', 'Statut')
                ->setChoices([
                    'En attente' => ReportStatus::Pending,
                    'Traité' => ReportStatus::Reviewed,
                    'Rejeté' => ReportStatus::Rejected,
                    'Résolu' => ReportStatus::Resolved
                ])
                ->renderAsBadges([
                    ReportStatus::Pending->value => 'warning',
                    ReportStatus::Reviewed->value => 'info',
                    ReportStatus::Rejected->value => 'success',
                    ReportStatus::Resolved->value => 'secondary'
                ]),
            TextareaField::new('adminNote', 'Note admin')->hideOnIndex(),
            DateTimeField::new('createdAt', 'Date')
                ->onlyOnIndex()
                ->hideOnForm()
        ];
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->remove(Crud::PAGE_INDEX, Action::NEW)
            ->setPermission(Action::DELETE, 'ROLE_ADMIN')
        ;
    }
}
