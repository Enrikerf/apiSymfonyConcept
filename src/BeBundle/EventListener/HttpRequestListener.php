<?php

namespace App\BeBundle\EventListener;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Security\Core\Security;


class HttpRequestListener {

    private Request            $request;

    public function __construct(RequestStack $requestStack) {
        $this->request = $requestStack->getCurrentRequest();
    }

    public function onKernelRequest(RequestEvent $event): void {
        if ($event->isMasterRequest()) {
            $this->processRequestContentForContentType($event );
        }
    }

    private function processRequestContentForContentType(RequestEvent $event): void {
        if (
            $this->containsHeader('Content-Type', 'application/json') &&
            !$this->contentIsEmpty()
        ) {
            $jsonData = json_decode($this->request->getContent(), true, 512, JSON_THROW_ON_ERROR);
            if (is_array($jsonData)) {
                $this->request->request->replace($jsonData);
            }
            if (is_bool($jsonData)) {
                $response = new Response('', Response::HTTP_BAD_REQUEST);
                $event->setResponse($response);
            }
        }
    }

    private function containsHeader($name, $value): bool {
        return 0 === strpos($this->request->headers->get($name), $value);
    }

    private function contentIsEmpty(){
        return ($this->request->getContent() == '')?true:false;
    }

}
