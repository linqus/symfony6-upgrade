<?php

namespace App\EventSubscriber;

use App\Entity\Question;
use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityUpdatedEvent;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class BlameableSubscriber implements EventSubscriberInterface
{
    public function __construct(public Security $security) {}

    public function onBeforeEntityUpdatedEvent(BeforeEntityUpdatedEvent $event): void
    {
        $question = $event->getEntityInstance();
        $user = $this->security->getUser();

        if (!$question instanceof Question ) {
            return;
        }
        
        if (!$user instanceof User) {
            throw \LogicalException('Wrong user!');
        }

        $question->setUpdatedBy($user);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            //BeforeEntityUpdatedEvent::class => 'onBeforeEntityUpdatedEvent',
        ];
    }
}
