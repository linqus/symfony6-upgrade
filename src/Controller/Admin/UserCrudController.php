<?php

namespace App\Controller\Admin;

use App\Entity\User;
use DateTime;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AvatarField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;

class UserCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    
    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')
                ->onlyOnIndex();
        yield AvatarField::new('avatar')
                ->formatValue(static function($value, ?User $user) {
                        return $user?->getAvatarUrl();
                })
                ->hideOnForm();
        yield ImageField::new('avatar')
                ->setUploadDir('public/uploads/avatars')
                ->setBasePath('uploads/avatars')
                ->setUploadedFileNamePattern('[slug]-[timestamp].[extension]')
                ->onlyOnForms();
        yield TextField::new('userName');
        yield TextField::new('fullName')
                ->hideOnForm();

        yield TextField::new('firstName')
                ->onlyOnForms();
        yield TextField::new('lastName')
                ->onlyOnForms();
        if ($pageName == 'index') {
            yield BooleanField::new('enabled')->renderAsSwitch(false);
        } else {
            yield BooleanField::new('enabled')->renderAsSwitch(true);
        }
        
        yield DateTimeField::new('createdAt')->hideOnForm();

        $roles = ['ROLE_ADMIN', 'ROLE_USER', 'ROLE_MODERATOR', 'ROLE_SUPER_ADMIN'];
        yield ChoiceField::new('roles')
                ->setChoices(array_combine($roles,$roles))
                //->renderExpanded()
                ->renderAsBadges()
                ->allowMultipleChoices();
    
    }
    
    public function configureCrud(Crud $crud): Crud
    {
        return $crud
                ->setEntityPermission('ADMIN_USER_EDIT');
    }
}
