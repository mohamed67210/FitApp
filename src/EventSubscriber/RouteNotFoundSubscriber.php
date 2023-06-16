<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class RouteNotFoundSubscriber implements EventSubscriberInterface
{
    private $urlGenerator ;
    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    public function onKernelException(ExceptionEvent $event){
        $exception = $event->getThrowable();
        if ($exception instanceof NotFoundHttpException) {
            // ------generer l'url ou on veut se deriger en cas d'erreur
            $redirectUrl = $this->urlGenerator->generate('app_404');
            $response = new RedirectResponse($redirectUrl);
            $event->setResponse($response);
        }
    }
    public static function getSubscribedEvents(){
        return [
            KernelEvents::EXCEPTION => 'onKernelException',
        ];
    }
}

?>