<?php

namespace App\BeBundle\Data\Cases;

use App\BeBundle\Data\Service\DataBaseService;


class DefaultDataCase {

    private DataBaseService $dbService;

    public function __construct(DataBaseService $dbService) {
        $this->dbService = $dbService;
    }

    public function filter(string $entityName, array $queryParam = []): array {
        return $this->dbService->filter($entityName, $queryParam);
    }

    public function getAllOf(string $entityName): array {
        return $this->dbService->findAllOf($entityName);
    }

    public function create(string $entityName, $data) {
        return $this->dbService->storeData($entityName, $data);
    }
}