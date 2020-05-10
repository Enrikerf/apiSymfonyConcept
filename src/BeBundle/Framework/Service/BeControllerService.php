<?php

namespace App\BeBundle\Framework\Service;

use App\BeBundle\Domain\Cases\DefaultDomainCase;
use App\BeBundle\Domain\Interfaces\DomainInterface;
use App\BeBundle\Framework\Interfaces\MiddlewareInterface;
use App\BeBundle\Framework\Interfaces\ViewManagerInterface;
use App\BeBundle\Framework\Middleware\DefaultMiddleware;
use App\BeBundle\Framework\Model\BeContext;
use App\BeBundle\Framework\View\Manager\DefaultViewManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


class BeControllerService {

    private string                $managedEntityName;
    private ?string               $managedEntityFormName = null;
    private ?string               $managedEntityAssert   = null;
    private ?MiddlewareInterface  $middleware            = null;
    private ?DomainInterface      $domain                = null;
    private ?ViewManagerInterface $viewManager           = null;

    public function __construct(
        DefaultMiddleware $defaultMiddleware,
        DefaultDomainCase $defaultDomain,
        DefaultViewManager $defaultViewManager
    ) {
        $this->domain = $defaultDomain;
        $this->middleware = $defaultMiddleware;
        $this->viewManager = $defaultViewManager;
    }

    public function getAllEndpoint(Request $request): Response {
        $context = new BeContext();
        $this->middleware->getAll($context, $request);
        $domainResponse = $this->domain->getAll($request);

        return $this->viewManager->getView($context,
            $request->query->get('fields'),
            $domainResponse->getStatusCode(),
            $domainResponse->getContent());
    }

    public function postEndpoint(Request $request): Response {
        $context = new BeContext();
        $this->middleware->post($context, $request);
        $domainResponse = $this->domain->post($request);

        return $this->viewManager->getView($context,
            $request->query->get('fields'),
            $domainResponse->getStatusCode(),
            $domainResponse->getContent());    }

    public function getManagedEntityName(): ?string {
        return $this->managedEntityName;
    }

    public function setManagedEntityName(string $managedEntityName): void {
        $this->managedEntityName = $managedEntityName;
        if (method_exists($this->domain, 'setManagedEntityName')) {
            $this->domain->setManagedEntityName($this->managedEntityName);
        }
    }

    public function getManagedEntityFormName(): ?string {
        return $this->managedEntityFormName;
    }

    public function setManagedEntityFormName(string $managedEntityFormName): BeControllerService {
        $this->managedEntityFormName = $managedEntityFormName;
        if (method_exists($this->domain, 'setManagedEntityFormName')) {
            $this->domain->setManagedEntityFormName($this->managedEntityFormName);
        }

        return $this;
    }

    public function getMiddleware(): ?MiddlewareInterface {
        return $this->middleware;
    }

    public function setMiddleware(?MiddlewareInterface $middleware): void {
        $this->middleware = $middleware;
    }

    public function getDomain(): ?DomainInterface {
        return $this->domain;
    }

    public function setDomain(?DomainInterface $domain): void {
        $this->domain = $domain;
    }

    public function getViewManager(): ?ViewManagerInterface {
        return $this->viewManager;
    }

    public function setViewManager(?ViewManagerInterface $viewManager): void {
        $this->viewManager = $viewManager;
    }
}