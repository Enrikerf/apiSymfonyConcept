<?php

namespace App\BeBundle\Data\Service;


use App\BeBundle\Data\Model\FilterService;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;
use Exception as Exception;


class DataBaseService {

    private EntityManagerInterface $entityManager;
    private ManagerRegistry        $managerRegistry;
    private FilterService         $filterService;

    public function __construct(
        EntityManagerInterface $entityManagerProvider,
        ManagerRegistry $managerRegistry,
        FilterService $filterService
    ) {
        $this->entityManager = $entityManagerProvider;
        $this->filterService = $filterService;
        $this->managerRegistry = $managerRegistry;
    }

    public function filter(string $entityName, array $queryParams = []): array {
        $this->filterService->configure($this->entityManager, $entityName);

        return $this->filterService->filter($queryParams);
    }

    public function findAllOf(string $entityName): array {
        return $this->managerRegistry->getRepository($entityName)->findAll();
    }

    public function storeData(string $entityName, $data) {
        try {
            $this->managerRegistry->getManager()->persist($data);
            $this->managerRegistry->getManager()->flush();
            $entityCreated = $this->managerRegistry->getRepository($entityName)->find($data);
        } catch (Exception $e) {
            return $e;
        }

        return $entityCreated;
    }
}