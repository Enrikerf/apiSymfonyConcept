<?php
/**
 * User: enrikerf
 * Date: 09/05/2017
 * Time: 12:09
 */

namespace App\BeBundle\Data\Model;

use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\ORM\Query\Expr;
use Exception;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use function count;
use function is_array;
use function is_int;
use function is_string;


class WbFilter {

    public const TYPE_WHERE = 1;
    public const TYPE_ORDER_BY = 2;
    public const TYPE_SELECT = 3;
    public const TYPE_GROUP_BY = 4;
    public const VAR_MINUTE = 'MINUTE';
    public const MAPPER_TIMER = [
        'm' => 'MINUTE',
        'h' => 'HOUR',
        'd' => 'DAY',
        'w' => 'WEEK',
        'M' => 'MONTH',
    ];
    public const MAPPER_OPERATOR = [
        '+' => '>=',
        '-' => '<=',
    ];
    public const MAPPER_DAY_CALCULATION = [
        'MINUTE' => 1,
        'HOUR' => 60,
        'DAY' => 1440,
        'WEEK' => 10080,
        'MONTH' => 43800,
    ];
    private ?int                           $type;
    private ?string                        $name;
    private ?string                        $bracket;
    private ?string                        $externLogic;
    private ?string                        $internLogic;
    private string                         $operator;
    private array                          $values;
    private Expr                           $queryLogic;

    /**
     * @param $name
     * @param $getParamValues
     *
     * @throws HttpException
     */
    public function setFilter($name, $getParamValues): void {
        if (!is_array($getParamValues)) {
            $getParamValues = [$getParamValues];
        }
        foreach ($getParamValues as $components) {
            /**
             *  0           1                          2
             *                        --------------------------------------
             *                          0          1              2
             * bracket,externalLogic,operator/internalLogic/value1;...;valueN
             */
            $filter = explode(',', $components);
            switch (count($filter)) {
                case 1:
                    $this->setPrivateVariables(
                        $name,
                        'AND',
                        'eq',
                        'AND',
                        [$filter[0]],
                        $this::TYPE_WHERE
                    );
                    break;
                case 2:
                case 4:
                    $operator_internLogic_values = explode('/', $filter[1]);
                    $values = explode(';', $operator_internLogic_values[2]);
                    foreach ($values as $value) {
                        $this->values [] = $value;
                    }
                    $this->setPrivateVariables(
                        $name,
                        $filter[0],
                        $operator_internLogic_values[0],
                        $operator_internLogic_values[1],
                        $values
                    );
                    break;
                case 3:
                    $bracket = $filter[0];
                    $operator_internLogic_values = explode('/', $filter[2]);
                    $values = explode(';', $operator_internLogic_values[2]);
                    foreach ($values as $value) {
                        $this->values [] = $value;
                    }
                    $this->setPrivateVariables(
                        $name,
                        $filter[1],
                        $operator_internLogic_values[0],
                        $operator_internLogic_values[1],
                        $values,
                        $bracket
                    );
                    break;
                default:
                    throw new HttpException(Response::HTTP_BAD_REQUEST, 'wrong url');
            }
        }
    }

    /**
     * @param      $name
     * @param      $externLogic
     * @param      $operator
     * @param      $internLogic
     * @param      $values
     * @param null $bracket
     * @param null $type
     *
     * @throws HttpException
     */
    public function setPrivateVariables(
        $name,
        $externLogic,
        $operator,
        $internLogic,
        $values,
        $bracket = null,
        $type = null
    ): void {
        if ($type) {
            $this->type = $type;
        } else {
            $this->getOperatorType($operator);
        }
        $this->bracket = $bracket;
        $this->name = $name;
        $this->externLogic = $externLogic;
        $this->operator = $operator;
        $this->internLogic = $internLogic;
        $this->values = $values;
    }

    /**
     * @param $operator
     *
     * @throws HttpException
     */
    public function getOperatorType($operator): void {
        switch ($operator) {
            case 'eq':
            case 'neq':
            case 'lk':
            case 'clk':
            case 'nlk':
            case 'lt':
            case 'lte':
            case 'gt':
            case 'gte':
            case 'in':
            case 'inn':
            case 'imo':
            case 'bc':
            case 'gtNow':
            case 'ltNow':
            case 'inarray':
            case 'notinarray':
            case 'btw': //between
            case 'btwInterval':
            case 'testA':
            case 'testB':
                $this->type = $this::TYPE_WHERE;
                break;
            case 'ob':
                $this->type = $this::TYPE_ORDER_BY;
                break;
            case 'gb':
                $this->type = $this::TYPE_GROUP_BY;
                break;
            case 'count':
            case 'sme': //selectMinimumEntity selecciona campos Mínimos para que siga siendo una instancia del objeto además de los que se les indique
            case 'sf': //selecciona los campos elegidos pero deja de ser una instancia
            case 'sfe': //selecciona los campos elegidos pero deja de ser una instancia
                $this->type = $this::TYPE_SELECT;
                break;
            default:
                throw new HttpException(Response::HTTP_BAD_REQUEST, 'wrong url');
        }
    }

    public function getType() {
        return $this->type;
    }

    public function setType($type): void {
        $this->type = $type;
    }

    public function getExternLogic() {
        return $this->externLogic;
    }

    public function setExternLogic($externLogic): void {
        $this->externLogic = $externLogic;
    }

    public function getInternLogic() {
        return $this->internLogic;
    }

    public function setInternLogic($internLogic): void {
        $this->internLogic = $internLogic;
    }

    public function getOperator() {
        return $this->operator;
    }

    public function setOperator($operator): void {
        $this->operator = $operator;
    }

    public function getValues() {
        return $this->values;
    }

    public function setValues($values): void {
        $this->values = $values;
    }

    public function getBracket() {
        return $this->bracket;
    }

    public function setBracket($bracket): void {
        $this->bracket = $bracket;
    }

    /**
     * @param $query
     * @param $queryLogic
     *
     * @throws HttpException
     * @throws Exception
     */
    public function addFilterToQuery($query, $queryLogic): void {
        $this->queryLogic = $queryLogic;
        switch ($this->type) {
            case $this::TYPE_WHERE:
                $this->addWhereExpression($query);
                break;
            case $this::TYPE_ORDER_BY:
                $this->addOrderByExpression($query);
                break;
            case $this::TYPE_GROUP_BY:
                $this->addGroupByExpression($query);
                break;
            case $this::TYPE_SELECT:
                $this->addSelectExpression($query);
                break;
            default:
                throw new HttpException(Response::HTTP_BAD_REQUEST, '[ WbFilter ] Bad filter');
        }
    }

    /**
     * @param QueryBuilder $query
     *
     * @throws HttpException
     * @throws Exception
     */
    public function addWhereExpression($query): void {
        $expression = '( ';
        $counter = 0;
        foreach ($this->values as $value) {
            $counter === 0 ?: $expression .= ' '.$this->internLogic.' ';
            switch ($this->operator) {
                case 'eq':
                    if (is_string($value) && ($value === '')) {
                        $expression .= $this->queryLogic->isNull('e.'.$this->getName());
                    } else {
                        $expression .= $this->queryLogic->eq('e.'.$this->getName(), "'".$value."'");
                    }
                    break;
                case 'neq':
                    if (is_string($value) && ($value === '')) {
                        $expression .= $this->queryLogic->isNotNull('e.'.$this->getName());
                    } else {
                        $expression .= $this->queryLogic->neq('e.'.$this->getName(), "'".$value."'");
                    }
                    break;
                case 'lk':
                    $expression .= $this->queryLogic->like('e.'.$this->getName(), "'".$value."'");
                    break;
                case 'clk':
                    $expression .= $this->queryLogic->like('e.'.$this->getName(), "'%".$value."%'");
                    break;
                case 'nlk':
                    $expression .= $this->queryLogic->notLike('e.'.$this->getName(), "'".$value."'");
                    break;
                case 'lt':
                    $expression .= $this->queryLogic->lt('e.'.$this->getName(), "'".$value."'");
                    break;
                case 'lte':
                    $expression .= $this->queryLogic->lte('e.'.$this->getName(), "'".$value."'");
                    break;
                case 'gt':
                    $expression .= $this->queryLogic->gt('e.'.$this->getName(), "'".$value."'");
                    break;
                case 'gte':
                    $expression .= $this->queryLogic->gte('e.'.$this->getName(), "'".$value."'");
                    break;
                case 'in':
                    $expression .= $this->queryLogic->isNull('e.'.$this->getName());
                    break;
                case 'inn':
                    $expression .= $this->queryLogic->isNotNull('e.'.$this->getName());
                    break;
                case 'gtNow':
                    // gtNow/-/1h(-x): x: time using the same type
                    if ($value !== '-') {
                        [$counter, $type, $counterLimit, $typeLimit] = $this->parseOperator($this->operator, $value);
                        $subDate = ' DATE_ADD(e.'.$this->getName().', '.$counter.', \''.$type.'\')';
                        $expression .= ' NOW() >= '.$subDate;
                        if ($counterLimit) {
                            $counterLimit = $this->parseToMinutes($counterLimit,
                                    $typeLimit) + $this->parseToMinutes($counter, $type);
                            $subDateLimit = ' DATE_ADD(e.'.$this->getName().', '.$counterLimit.', \''.self::VAR_MINUTE.'\')';
                            $expression .= ' AND NOW() < '.$subDateLimit;
                        }
                    } else {
                        $expression .= 'e.'.$this->getName().' >= NOW()';
                    }
                    break;
                case 'ltNow':
                    // ltNow/-/1h
                    if ($value !== '-') {
                        [$counter, $type, $counterLimit, $typeLimit] = $this->parseOperator($this->operator, $value);
                        $subDate = ' DATE_SUB(e.'.$this->getName().', '.$counter.', \''.$type.'\')';
                        $expression .= ' NOW() >= '.$subDate.' AND NOW() <= e.'.$this->getName();
                        if ($counterLimit) {
                            $counterLimit = $this->parseToMinutes($counter,
                                    $type) - $this->parseToMinutes($counterLimit, $typeLimit);
                            $subDateLimit = ' DATE_SUB(e.'.$this->getName().', '.$counterLimit.', \''.self::VAR_MINUTE.'\')';
                            $expression .= ' AND NOW() <'.$subDateLimit;
                        }
                    } else {
                        $expression .= 'e.'.$this->getName().' <= NOW()';
                    }
                    break;
                case 'imo':
                    $param = 'rand_'.substr(md5(random_int(1, 10000)), 0, 10);
                    $expression .= $this->queryLogic->isMemberOf(':'.$param, 'e.'.$this->getName());
                    $query->setParameter($param, $value);
                    break;
                case 'btw':
                    $xvalues = explode('|', $value);
                    $expression .= $this->queryLogic->andX(
                        $this->queryLogic->gte('e.'.$this->getName(), "'".$xvalues[0]."'"),
                        $this->queryLogic->lte('e.'.$this->getName(), "'".$xvalues[1]."'")
                    );
                    break;
                case 'bc':
                    $expression .= ' BIT_COUNT(BIT_AND(e.'.$this->getName().','.$value.' ) ) >=1';
                    break;
                case 'notinarray':
                    $formattedValue = str_replace(";", ",", $value);
                    $expression .= $this->queryLogic->notIn('e.'.$this->getName(), "'".$formattedValue."'");
                    break;
                case 'inarray':
                    $expression .= $this->queryLogic->in('e.'.$this->getName(), "'".$value."'");
                    break;
                case 'testA':
                    $expression .= 'MOD(UNIX_TIMESTAMP(e.'.$this->getName().'), 2) = 0';
                    break;
                case 'testB':
                    $expression .= 'MOD(UNIX_TIMESTAMP(e.'.$this->getName().'), 2) <> 0';
                    break;
                case 'btwInterval':
                    // AND,btwInterval/-/finishDate(2019-11-19|2019-11-22)
                    $firstField = $this->getName();
                    preg_match('/[a-zA-Z]+/', $value, $gettingLastField);
                    $lastField = $gettingLastField[0];
                    preg_match('/[^a-zA-Z]+/', $value, $gettingValues);
                    $possibleValues = explode('|', $gettingValues[0]);
                    $firstValue = '\''.ltrim($possibleValues[0], '(').'\'';
                    $lastValue = '\''.rtrim($possibleValues[1], ')').'\'';
                    $expression .= '('.$lastValue.' <= e.'.$lastField.' AND '.$lastValue.' >= e.'.$firstField.') OR ('.$lastValue.' >= e.'.$lastField.' AND '.$firstValue.' >= e.'.$firstField.' AND '.$firstValue.' <= e.'.$lastField.') OR ('.$lastValue.' >= e.'.$lastField.' AND '.$firstValue.' <= e.'.$firstField.')';
                    break;
                default:
                    throw new HttpException(Response::HTTP_BAD_REQUEST, 'wrong url');
            }
            $counter++;
        }
        $expression .= ' )';
        switch ($this->externLogic) {
            case 'AND':
                $query->andWhere($expression);
                break;
            case 'OR':
                $query->orWhere($expression);
                break;
            default:
                throw new HttpException(Response::HTTP_BAD_REQUEST, 'wrong url');
        }
    }

    public function getName() {
        return $this->name;
    }

    public function setName($name): void {
        $this->name = $name;
    }

    /**
     * @param $operator
     * @param $composeValues
     *
     * @return array
     * @throws HttpException
     */
    private function parseOperator($operator, $composeValues): array {
        switch ($operator) {
            case 'gtNow':
            case 'ltNow':
                $parts = explode('-', $composeValues);
                $firstPart = $parts[0];
                $limitPart = $parts[1] ?? null;
                $limitCounter = null;
                $limitMapperType = null;
                [$counter, $mapperType] = $this->parseFormatBackwardForwardType($firstPart);
                if ($limitPart) {
                    [$limitCounter, $limitMapperType] = $this->parseFormatBackwardForwardType($limitPart);
                }

                return [$counter, $mapperType, $limitCounter, $limitMapperType];
        }

        return [0, self::MAPPER_TIMER['m'], 0, self::MAPPER_TIMER['m']];
    }

    /**
     * @param $part
     *
     * @return array
     * @throws HttpException
     */
    private function parseFormatBackwardForwardType($part): array {
        $counter = substr($part, 0, -1);
        if (!is_int((int)$counter)) {
            throw new HttpException(Response::HTTP_BAD_REQUEST,
                'wrong gtNow/ltNow filter composed. Expected int value');
        }
        $typeTime = substr($part, -1);
        if (!isset(self::MAPPER_TIMER[$typeTime])) {
            throw new HttpException(
                Response::HTTP_BAD_REQUEST,
                'wrong gtNow/ltNow filter composed. Expected [m, h, d , w, M] values'
            );
        }
        $mapperType = self::MAPPER_TIMER[$typeTime];

        return [$counter, $mapperType];
    }

    private function parseToMinutes($counterLimit, $typeLimit) {
        return $counterLimit * self::MAPPER_DAY_CALCULATION[$typeLimit];
    }

    /**
     * @param QueryBuilder $query
     *
     * @throws HttpException
     */
    public function addOrderByExpression($query): void {
        foreach ($this->values as $value) {
            if ($this->operator === 'ob') {
                $query->addOrderBy('e.'.$this->getName(), $value);
            } else {
                throw new HttpException(Response::HTTP_BAD_REQUEST, 'wrong url');
            }
        }
    }

    /**
     * @param QueryBuilder $query
     *
     * @throws HttpException
     */
    public function addGroupByExpression($query): void {
        foreach ($this->values as $value) {
            if ($this->operator === 'gb') {
                $query->addGroupBy('e.'.$this->getName());
            } else {
                throw new HttpException(Response::HTTP_BAD_REQUEST, 'wrong url');
            }
        }
    }

    /**
     * @param QueryBuilder $query
     *
     * @throws HttpException
     */
    public function addSelectExpression($query): void {
        $sfValues = null;
        foreach ($this->values as $value) {
            switch ($this->operator) {
                case 'count':
                    $query->select($this->queryLogic->count('e.'.$this->name));
                    break;
                case 'sfe':
                    $query->addSelect('IDENTITY(e.'.$value.') as '.$value);
                    break;
                case 'sf':
                    $query->addSelect('e.'.$value);
                    break;
                case 'sme':
                    $sfValues .= ','.$value;
                    break;
                default:
                    throw new HttpException(Response::HTTP_BAD_REQUEST, 'wrong url');
            }
        }
        if ($sfValues) {
            $query->addSelect('partial e.{id'.$sfValues.'}');
        }
    }
}