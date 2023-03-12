<?php

namespace App\Controller\Admin;

use App\Entity\Question;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;

class PendingApprovalCrudController extends QuestionCrudController
{
    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        return parent::createIndexQueryBuilder($searchDto,$entityDto,$fields,$filters)
                ->andWhere('entity.isApproved = :isApproved')
                ->setParameter('isApproved',false);
    }

    public function configureFields(string $pageName): iterable
    {
        foreach (parent::configureFields($pageName) as $field)
        {
            yield $field;
        }
        yield BooleanField::new('isApproved')
                 ->renderAsSwitch();
    }

    public function configureCrud(Crud $crud): Crud
    {
        return parent::configureCrud($crud)
                ->setPageTitle(Crud::PAGE_INDEX,'Questions Pending Approval')
                ->setPageTitle(Crud::PAGE_DETAIL,function(Question $question) {
                    return sprintf("#%s %s",$question->getId(), $question->getName());
                })
                ->setHelp(Crud::PAGE_INDEX,'Questions are not published to users until are approved');
    }
}