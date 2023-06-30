<?php

namespace Emma\ORM\Repository;

use Emma\Dbal\QueryBuilder\Expressions\WhereCompositeCondition;
use Emma\Dbal\QueryBuilder\Expressions\WhereCondition;
use Emma\ORM\Repository\Helpers\OperatorHelper;
use Emma\ORM\Repository\Helpers\ParameterHelper;
use Emma\ORM\Repository\Interfaces\CrudRepositoryInterface;
use function str_contains;

class AnonymousMethodHandler
{
    use ParameterHelper;
    use OperatorHelper;

    /**
     * @param array $entityPropertyNames
     * @param $methodName
     * @param $arguments
     * @param CrudRepositoryInterface $repository
     * @return mixed
     */
    public function call(array $entityPropertyNames, $methodName, $arguments, CrudRepositoryInterface $repository): mixed
    {
        foreach (self::SUPPORTED_METHODS as $METHOD) {
            if (str_contains($methodName, $METHOD)) {
                $whereCondition = $this->getWhereConditions($entityPropertyNames, $methodName, $arguments);
                return $repository->$METHOD($whereCondition);
            }
        }
        throw new \InvalidArgumentException("Invalid/Unsupported Function Call {$methodName}");
    }

    /**
     * @param array $entityPropertyNames
     * @param $methodName
     * @param $arguments
     * @return WhereCondition[]|false|mixed
     */
    public function getWhereConditions(array $entityPropertyNames, $methodName, $arguments): mixed
    {
        $fields = $this->getParameterNames($entityPropertyNames, $methodName);
        $operators = $this->getOperators($this->parameterString);

        $numberOfOperators = count($operators);
        if ($numberOfOperators <= 0) {
            $field = $fields[0];
            $value = $arguments[0];
            return is_array($value) ? [WhereCondition::in($field, $value)] : [WhereCondition::eq($field, $value)];
        } else {
            $whereConditions = [];
            $_2condition = [];
            $count = 0;
            $size = count($arguments);
            for ($i = 0; $i < $size; $i++) {
                $field = $fields[$i];
                $value = $arguments[$i];
                $operand = is_array($value) ? "IN" : "=";
                $concatOperand = $i > 0 ? strtoupper($operators[$i - 1]) : null;
                $_2condition[] = WhereCondition::compose($field, $operand, $value);

                $count++;
                if ($count==2) {
                    $whereConditions = WhereCompositeCondition::compose(
                        $_2condition,
                        $concatOperand
                    );
                    $_2condition = [];
                    $count = 0;
                }
                if ($count==1 && $i == ($size-1) ) {
                    $container = [$whereConditions, WhereCondition::compose($field, $operand, $value)];
                    $whereConditions = WhereCompositeCondition::compose($container, $concatOperand);
                }
            }
            return reset($whereConditions);
        }
    }



}