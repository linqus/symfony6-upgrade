<?php

namespace App\Controller\Admin;

use App\EasyAdmin\VoteField;
use App\Entity\Answer;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class AnswerCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Answer::class;
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')
            ->onlyOnIndex();
        yield Field::new('answer');
        yield VoteField::new('votes')
                ->setTemplatePath('admin/field/votes.html.twig');
        yield AssociationField::new('question')
            ->hideOnIndex();
        yield AssociationField::new('answeredBy');
        yield Field::new('createdAt')
            ->hideOnForm();
        yield Field::new('updatedAt')
            ->onlyOnDetail();
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud->setDefaultSort([
            'answeredBy.enabled' => 'DESC',
            'createdAt' => 'DESC'
        ]);
    }
}
