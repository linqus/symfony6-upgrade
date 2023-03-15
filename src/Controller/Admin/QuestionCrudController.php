<?php

namespace App\Controller\Admin;

use App\EasyAdmin\VoteField;
use App\Entity\Question;
use App\Service\CsvExporter;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Factory\FilterFactory;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_MODERATOR')]
class QuestionCrudController extends AbstractCrudController
{
    public function __construct(private AdminUrlGenerator $adminUrlGenerator,private RequestStack $requestStack) {}

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
        yield BooleanField::new('isApproved')
                ->hideOnIndex();
        yield Field::new('createdAt')
                ->hideOnForm();
        yield AssociationField::new('updatedBy')
                ->onlyOnDetail();
    }

    public function configureActions(Actions $actions): Actions
    {
        
        $viewAction = function() {
            return Action::new('view')
                    ->linkToUrl(function (Question $question) {
                        return $this->generateUrl('app_question_show',[
                                    'slug' => $question->getSlug(),
                        ]);
                    })
                    ->setIcon('fa fa-eye')
                    ->setLabel('View on page');
        };
            
        $approveAction = Action::new('approve')
            ->setIcon('fas fa-check-circle')
            ->setLabel('Approve')
            ->displayAsButton()
            ->setTemplatePath('admin/approve_action.html.twig')
            ->linkToCrudAction('approve')
            ->displayIf(static function(Question $question) {
                return !$question->getIsApproved();
            });

       $exportAction = Action::new('export')
            ->linkToUrl(function() {
                $request = $this->requestStack->getCurrentRequest();

                return $this->adminUrlGenerator
                        ->setAll($request->query->all())
                        ->setAction('export')
                        ->generateUrl();
                    
            })
            ->addCssClass('btn btn-success')
            ->setIcon('fas fa-download')
            ->setLabel('Eksport')
            //->displayAsButton()
            ->createAsGlobalAction();


        $newActions = $actions
/*         ->update(Crud::PAGE_INDEX,Action::DELETE, function(Action $action) {
            $action->displayIf(function(Question $question) {
                return !$question->getIsApproved();
            });
            //dd($action);
            return $action;
        }) */       
        ->setPermission(Action::INDEX,'ROLE_MODERATOR')
        ->setPermission(Action::EDIT,'ROLE_MODERATOR')
        ->setPermission(Action::DETAIL,'ROLE_MODERATOR')
        ->setPermission(Action::DELETE,'ROLE_SUPER_ADMIN')
        ->setPermission(Action::BATCH_DELETE,'ROLE_SUPER_ADMIN')
        ->setPermission(Action::NEW,'ROLE_SUPER_ADMIN')
        ->disable(Action::BATCH_DELETE)
        ->add(Crud::PAGE_DETAIL,$viewAction()->addCssClass('btn btn-success'))
        ->add(Crud::PAGE_INDEX, $viewAction())
        ->add(Crud::PAGE_DETAIL, $approveAction)
        ->add(Crud::PAGE_INDEX,$exportAction)
        ->reorder(Crud::PAGE_DETAIL,[
            'approve',
            'view',
            Action::INDEX,
            Action::EDIT,
            Action::DELETE
        ]);
            


        return $newActions;
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

    /**
     * @param Question $entityInstance
     */
    public function deleteEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if ($entityInstance->getIsApproved()) {
            throw new \Exception("Cannot delete approved question");
        }

        parent::deleteEntity($entityManager,$entityInstance);

    }

    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        
        $user = $this->getUser();

        $entityInstance->setUpdatedBy($user);

        parent::updateEntity($entityManager, $entityInstance);
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
               ->setDefaultSort(['votes' => 'DESC'])
               ->showEntityActionsInlined();
    }

    public function approve(AdminContext $adminContext, EntityManagerInterface $entityManager, AdminUrlGenerator $adminUrlGenerator) 
    {
        $question = $adminContext->getEntity()->getInstance();

        if (!$question instanceof Question) {
            throw new \Exception('No question found');
        }

        $question->setIsApproved(true);
        $entityManager->flush();

        $url = $adminUrlGenerator
                ->setController(self::class)
                ->setAction(Crud::PAGE_DETAIL)
                ->setEntityId($question->getId())
                ->generateUrl();
        return $this->redirect($url);
    }

    public function export(AdminContext $context, CsvExporter $csvExporter) 
    {
        $fields = FieldCollection::new($this->configureFields(Crud::PAGE_INDEX));
        $context->getCrud()->setFieldAssets($this->getFieldAssets($fields));
        $filters = $this->container->get(FilterFactory::class)->create($context->getCrud()->getFiltersConfig(), $fields, $context->getEntity());
        $queryBuilder = $this->createIndexQueryBuilder($context->getSearch(), $context->getEntity(), $fields, $filters);

        return $csvExporter->createResponseFromQueryBuilder(
            $queryBuilder,
            $fields,
            'questions.csv'
        );
    }
}
