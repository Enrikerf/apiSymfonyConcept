<?php
/** base repository */

namespace App\BeBundle\Data\Model;


use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use function array_key_exists;
use function in_array;


class FilterService {

    private bool                   $countFlag;
    private QueryBuilder           $queryBuilder;
    private Query\Expr             $queryLogic;
    private int                    $paginationLimit;
    private int                    $defaultPagination;
    private QueryBuilder           $query;
    private ?array                 $getParameters;
    private ?array                 $defaultGetParameters;
    private ?array                 $customGetParameters;
    private ?array                 $defaultGetFilters;
    private ?array                 $customGetFilters;
    private Logger                 $beLogger;
    private EntityManagerInterface $_em;
    private ClassMetadata          $_class;
    private string                 $_entityName;

    /**
     * public function __construct(EntityManager $entityManager, ClassMetadata $class) {
     * }
     */
    public function getEntityManager(): EntityManagerInterface {
        return $this->_em;
    }

    public function setPaginationLimit($paginationLimit): void {
        $this->paginationLimit = $paginationLimit;
    }

    public function setDefaultPagination($defaultPagination): void {
        $this->defaultPagination = $defaultPagination;
    }

    public function getPaginationLimit() {
        return $this->paginationLimit;
    }

    public function configure(EntityManagerInterface $entityManager, string $entityName) {
        $this->_em = $entityManager;
        $this->_class = $this->_em->getClassMetadata($entityName);
        $this->_entityName = $this->_class->name;
        $this->queryBuilder = $entityManager->createQueryBuilder();
        $this->queryLogic = $this->queryBuilder->expr();
        $this->paginationLimit = 50;
        $this->defaultPagination = 20;
        $this->defaultGetParameters = [];
        $this->customGetParameters = [];
        $this->defaultGetFilters = [];
        $this->customGetFilters = [];
        $this->countFlag = false;
        $this->beLogger = new Logger('logBE');
        $this->beLogger->pushHandler(new StreamHandler(__DIR__.'/../../var/log/prod/debug.log',
            Logger::DEBUG));
    }

    public function filter(array $getParams = []) {
        $this->setSelectBase($this->_entityName, $getParams);
        $this->setGetParameters($getParams);
        $this->setDefaultGetParametersFromGetParameters();
        $this->setCustomGetParametersFromGetParameters();
        $this->setDefaultGetFilters();
        if ($this->defaultGetFilters) {
            foreach ($this->defaultGetFilters as $filter) {
                $filter->addFilterToQuery($this->query, $this->queryLogic);
            }
        }
        $result = $this->query->getQuery()->execute();
        if ($this->countFlag) {
            $result = ['total' => $result[0]['1']];
        }
        $this->clearVariables();

        return $result;
    }

    private function setSelectBase($entityName, $manualParam = null): void {
        if (isset($_GET['page']) && isset($_GET['pageSize'])) {
            $limit = (int)$_GET['pageSize'];
            ($limit <= $this->paginationLimit) ?: $limit = $this->paginationLimit;
            $page = (int)$_GET['page'];
            ($page === 0) ? $offset = 0 : $offset = $limit * $page;
        } else {
            $offset = 0;
            $limit = $this->defaultPagination;
        }
        if ($manualParam && isset($manualParam['page']) && isset($manualParam['pageSize'])) {
            $limit = (int)$manualParam['pageSize'];
            $page = (int)$manualParam['page'];
            ($page === 0) ? $offset = 0 : $offset = $limit * $page;
        }
        $this->query = $this->getEntityManager()
            ->createQueryBuilder()->select()
            ->from($entityName, 'e')
            ->setFirstResult($offset)
            ->setMaxResults($limit);
    }

    private function setGetParameters(array $getParams): void {
        if ($getParams) {
            $this->getParameters = array_filter($getParams,
                function ($value) {
                    return ($value !== '' && $value !== null);
                });
        } else {
            $this->getParameters = [];
        }
    }

    private function setDefaultGetParametersFromGetParameters(): void {
        foreach ($this->getParameters as $getParamName => $getParamValue) {
            if (
                array_key_exists($getParamName, $this->_class->associationMappings) ||
                strcmp($getParamName, 'selectFields') === 0 ||
                strcmp($getParamName, 'selectEntities') === 0 ||
                strcmp($getParamName, 'count') === 0 ||
                in_array($getParamName, $this->_class->getFieldNames(), false)
            ) {
                if (strcmp($getParamValue, '-,count/-/-') === 0) {
                    $this->countFlag = true;
                }
                $this->defaultGetParameters[$getParamName] = $getParamValue;
            }
        }
    }

    private function setCustomGetParametersFromGetParameters(): void {
        foreach ($this->getParameters as $getParamName => $getParamValue) {
            if (!in_array($getParamName, $this->_class->getFieldNames(), false)) {
                $this->customGetParameters[$getParamName] = $getParamValue;
            }
        }
    }

    private function setDefaultGetFilters(): void {
        $customSelect = false;
        foreach ($this->defaultGetParameters as $getParamName => $getParamValues) {
            $filter = new WbFilter();
            $filter->setFilter($getParamName, $getParamValues);
            $this->defaultGetFilters[] = $filter;
            (strcmp($filter->getName(), 'selectFields') !== 0) ?: $customSelect = true;
            (strcmp($filter->getName(), 'selectEntities') !== 0) ?: $customSelect = true;
        }
        $customSelect ?: $this->query->addSelect('e');
    }

    private function clearVariables(): void {
        $this->getParameters = [];
        $this->defaultGetParameters = [];
        $this->customGetParameters = [];
        $this->defaultGetFilters = [];
        $this->customGetFilters = [];
        $this->queryBuilder->resetDQLParts();
        $this->query->resetDQLParts();
        $this->queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $this->queryLogic = $this->queryBuilder->expr();
        $this->setSelectBase($this->_entityName);
    }

    private function getPaginationByRequest($getParameters): array {
        if (isset($getParameters['page'], $getParameters['pageSize'])) {
            $limit = (int)$getParameters['pageSize'];
            ($limit <= $this->paginationLimit) ?: $limit = $this->paginationLimit;
            $page = (int)$getParameters['page'];
            ($page === 0) ? $offset = 0 : $offset = $limit * $page;
        } else {
            $offset = 0;
            $limit = $this->defaultPagination;
        }

        return [$offset, $limit];
    }
}