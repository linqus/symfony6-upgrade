<?php

namespace App\Controller\Admin;

use App\EasyAdmin\VoteField;
use App\Entity\Question;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_MODERATOR')]
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
                ->setFormTypeOptions([
                    'row_attr' => [
                        'data-controller' => 'snarkdown',
                    ],
                    'attr'=>[
                        'data-snarkdown-target'=>'input',
                        'data-action'=>'snarkdown#render'
                    ]
                ])
                ->setHelp('Preview:')
                ->hideOnIndex();
        yield VoteField::new('votes','Total Votes')
                ->setTextAlign('right')
                ->setPermission('ROLE_SUPER_ADMIN');
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

    public function configureActions(Actions $actions): Actions
    {
        return $actions
                ->setPermission(Action::INDEX,'ROLE_MODERATOR')
                ->setPermission(Action::EDIT,'ROLE_MODERATOR')
                ->setPermission(Action::DETAIL,'ROLE_MODERATOR')
                ->setPermission(Action::DELETE,'ROLE_SUPER_ADMIN')
                ->setPermission(Action::BATCH_DELETE,'ROLE_SUPER_ADMIN')
                ->setPermission(Action::NEW,'ROLE_SUPER_ADMIN');
            }

    public function configureFilters(Filters $filters): Filters
    {
        return parent::configureFilters($filters)
                ->add('name')
                ->add('createdAt')
                ->add('votes')
                ->add('answers')
                ->add('askedBy');
    }
}
