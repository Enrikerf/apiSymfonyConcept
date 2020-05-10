<?php

namespace App\Domain;

use App\BeBundle\BeLogger;
use App\Data\DefaultData;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;


class DefaultDomain implements DomainInterface {

    private ?string              $managedEntityName     = null;
    private ?string              $managedEntityFormName = null;
    private DefaultData          $defaultData;
    private FormFactoryInterface $formFactory;
    private BeLogger             $logger;

    public function __construct(DefaultData $defaultData, FormFactoryInterface $formFactory, BeLogger $logger) {
        $this->defaultData = $defaultData;
        $this->formFactory = $formFactory;
        $this->logger = $logger;
        $this->logger->setInvokerClass(static::class);
    }

    public function getAll(Request $request): array {
        if ($this->managedEntityName) {
            $entities = $this->defaultData->filter($this->managedEntityName, $request->query->all());
        } else {
            $this->logger->warning("Default Domain on getAll without managedEntityName setted. not even try, return [] by default");
            $entities = [];
        }

        return $entities;
    }

    public function post(Request $request) {
        $entity = $this->newEntity();
        if (!$this->validatorManager($entity, $request, 'POST')) {
            return null;
        }

        return $this->defaultData->create($this->managedEntityName, $entity);
    }

    private function validatorManager($entity, Request $request, string $method): bool {
        $parameters = $request->request->all();
        if ($this->managedEntityFormName) {
            $form = $this->formFactory->create($this->managedEntityFormName, $entity);
            $form->submit($parameters, $method);
            $formValid = $form->isValid();
            if (!$formValid) {
                //!$method ? $method = 'PATCH' : null;
                //$this->beService->getLogger()->error($method.' - Bad Request - Not valid form');
                return false;
            }
        }

        return true;
    }

    public function setManagedEntityName(?string $managedEntityName) {
        $this->managedEntityName = $managedEntityName;
    }

    public function setManagedEntityFormName(?string $managedEntityFormName) {
        $this->managedEntityFormName = $managedEntityFormName;
    }

    private function newEntity() {
        return new $this->managedEntityName;
    }
}