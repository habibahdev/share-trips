<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Enum\UserStatus;
use App\Service\MailService;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;

/**
 * @extends AbstractCrudController<User>
 */
class UserCrudController extends AbstractCrudController
{
    public function __construct(private MailService $mailer)
    {
    }

    public function updateEntity(EntityManagerInterface $entityManager, object $entityInstance): void
    {
        if (!$entityInstance instanceof User) {
            parent::updateEntity($entityManager, $entityInstance);
            return;
        }

        $originalData = $entityManager->getUnitOfWork()->getOriginalEntityData($entityInstance);
        $originalRaw = $originalData['status'] ?? null;
        $originalStatus = $originalRaw instanceof UserStatus
            ? $originalRaw
            : ($originalRaw !== null ? UserStatus::from($originalRaw) : null);
        parent::updateEntity($entityManager, $entityInstance);
        if ($originalStatus !== $entityInstance->getStatus()) {
            if ($entityInstance->isBanned()) {
                $this->mailer->sendBan($entityInstance);
            } elseif ($entityInstance->isSuspended()) {
                $this->mailer->sendSuspension($entityInstance);
            }
        }
    }

    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInPlural('Utilisateurs')
            ->setEntityLabelInSingular('Utilisateur')
            ->setDefaultSort(['id' => 'DESC'])
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        $disabled = $pageName === Crud::PAGE_EDIT;
        return [
            IdField::new('id')->onlyOnIndex(),
            TextField::new('firstName', 'Prénom')
                ->setFormTypeOption('disabled', $disabled),
            TextField::new('lastName', 'Nom')
                ->setFormTypeOption('disabled', $disabled),
            EmailField::new('email', 'Adresse e-mail')
                ->setFormTypeOption('disabled', $disabled),
            ChoiceField::new('status', 'Statut')
                ->setChoices([
                    'Actif' => UserStatus::Active,
                    'Suspendu' => UserStatus::Suspended,
                    'Banni' => UserStatus::Banned
                ])
                ->renderAsBadges([
                    UserStatus::Active->value => 'success',
                    UserStatus::Suspended->value => 'warning',
                    UserStatus::Banned->value => 'danger'
                ]),
            DateField::new('suspendedUntil', 'Suspendu jusqu\'au')->hideOnIndex(),
            TextareaField::new('adminNote', 'Note admin')->hideOnIndex(),
            DateTimeField::new('createdAt', 'Inscrit le')->onlyOnIndex()->hideOnForm()
        ];
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->remove(Crud::PAGE_INDEX, Action::DELETE)
            ->remove(Crud::PAGE_INDEX, Action::NEW)
            ->setPermission(Action::DELETE, 'ROLE_ADMIN')
        ;
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(TextFilter::new('email'))
            ->add(ChoiceFilter::new('status')->setChoices([
                'Actif' => UserStatus::Active,
                'Suspendu' => UserStatus::Suspended,
                'Banni' => UserStatus::Banned
            ]))
        ;
    }
}
