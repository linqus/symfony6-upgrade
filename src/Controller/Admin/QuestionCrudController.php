<?php

namespace App\Controller\Admin;

use App\EasyAdmin\VoteField;
use App\Entity\Question;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class QuestionCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Question::class;
    }


    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')
                ->onlyOnIndex();
        yield Field::new('name')
                ->setSortable(false);
        yield Field::new('slug')
                ->hideOnIndex()
                ->setFormTypeOptions([
                    'disabled' => $pageName !== crud::PAGE_NEW
                ]);
        yield AssociationField::new('topic');
        yield TextareaField::new('question')
                ->hideOnIndex();
        yield VoteField::new('votes','Total Votes')
                ->setTextAlign('right');
        yield AssociationField::new('askedBy')
                ->formatValue(static function($value, Question $question){
                    if (! $user=$question->getAskedBy()) {
                        return null;
                    }

                    return sprintf("%s&nbsp;(%s)",$user->getEmail(),$user->getQuestions()->count());
                })
                ->autocomplete()
                ->setQueryBuilder(function(QueryBuilder $queryBuilder) {
                     $queryBuilder
                            ->andWhere('entity.enabled = :isEnabled')
                            ->setParameter('isEnabled',true);
                });
        yield AssociationField::new('answers')
                ->autocomplete()
                ->setFormTypeOptions([
                    'by_reference'=>false
                ]);
        yield Field::new('createdAt')
                ->hideOnForm();
    }

}
