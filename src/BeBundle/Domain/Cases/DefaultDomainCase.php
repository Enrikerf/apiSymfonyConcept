<?php

namespace App\BeBundle\Domain\Cases;

use App\BeBundle\Data\Cases\DefaultDataCase;
use App\BeBundle\Domain\Interfaces\DomainInterface;
use App\BeBundle\Domain\Model\DomainResponse;
use App\BeBundle\Service\BeLogger;

use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;


class DefaultDomainCase implements DomainInterface {

    private DomainResponse               $response;
    private ?string                      $managedEntityName     = null;
    private ?string                      $managedEntityFormName = null;
    private DefaultDataCase                  $defaultData;
    private FormFactoryInterface         $formFactory;
    private BeLogger                     $logger;

    public function __construct(DefaultDataCase $defaultData, FormFactoryInterface $formFactory, BeLogger $logger) {
        $this->defaultData = $defaultData;
        $this->formFactory = $formFactory;
        $this->logger = $logger;
        $this->logger->setInvokerClass(static::class);
        $this->response = new DomainResponse();
    }

    public function getAll(Request $request): DomainResponse {
        if ($this->ensuredManagedEntityName()) {
            $entities = $this->defaultData->filter($this->managedEntityName, $request->query->all());
            $this->response->setContent($entities)->setStatusCode(200);
        } else {
            $this->logger->warning("Default Domain on getAll without managedEntityName setted. not even try, return [] by default");
            $this->response
                ->setContent(["message" => "Default Domain on getAll without managedEntityName setted. not even try, return [] by default"])
                ->setStatusCode(409);
        }

        return $this->response;
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

    private function ensuredManagedEntityName() {
        if (!class_exists($this->managedEntityName)) {
            if (!class_exists('App\\Data\\Entity\\'.ucfirst($this->managedEntityName))) {
                return false;
            } else {
                $this->managedEntityName = 'App\\Data\\Entity\\'.ucfirst($this->managedEntityName);

                return true;
            }
        }

        return true;
    }
}