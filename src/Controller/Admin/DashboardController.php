<?php

namespace App\Controller\Admin;

use App\Entity\Answer;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use LDAP\Result;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Entity\Question;
use App\Entity\Topic;
use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use Symfony\Component\Security\Core\User\UserInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\UserMenu;

class DashboardController extends AbstractDashboardController
{
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/admin', name: 'admin')]
    public function index(): Response
    {
        //return parent::index();
        return $this->render('admin/index.html.twig');
        // Option 1. You can make your dashboard redirect to some common page of your backend
        //
        // $adminUrlGenerator = $this->container->get(AdminUrlGenerator::class);
        // return $this->redirect($adminUrlGenerator->setController(OneOfYourCrudController::class)->generateUrl());

        // Option 2. You can make your dashboard redirect to different pages depending on the user
        //
        // if ('jane' === $this->getUser()->getUsername()) {
        //     return $this->redirect('...');
        // }

        // Option 3. You can render some custom template to display a proper dashboard with widgets, etc.
        // (tip: it's easier if your template extends from @EasyAdmin/page/content.html.twig)
        //
        // return $this->render('some/path/my-dashboard.html.twig');
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Cauldron Overflow Admin');
    }

    public function configureUserMenu(UserInterface $user): UserMenu
    {
        if (!$user instanceof User) {
            throw new \Exception('Wrong user!');
        } 

        return parent::configureUserMenu($user)
                ->setAvatarUrl($user->getAvatarUrl())
                ->setMenuItems([
                    MenuItem::linkToUrl('My profile','fas fa-user',$this->generateUrl('app_profile_show'))
                ]);
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Dashboard', 'fas fa-dashboard');
        yield MenuItem::linkToCrud('Questions', 'fa fa-question-circle', Question::class);
        yield MenuItem::linkToCrud('Answers', 'fas fa-comments', Answer::class);
        yield MenuItem::linkToCrud('Topics', 'fas fa-folder', Topic::class);
        yield MenuItem::linkToCrud('Users', 'fas fa-users', User::class);
        yield MenuItem::linkToUrl('Homepage','fas fa-home',$this->generateUrl('app_homepage'));
        
        // yield MenuItem::linkToCrud('The Label', 'fas fa-list', EntityClass::class);
    }

    public function configureAssets(): Assets
    {
        return parent::configureAssets()
                ->addWebpackEncoreEntry('admin');
    }

    public function configureActions(): Actions
    {
        return parent::configureActions()->add(Crud::PAGE_INDEX, Action::DETAIL);
    }
}
