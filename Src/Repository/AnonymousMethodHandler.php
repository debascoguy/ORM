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
            $jointOperators = [];
            $size = count($fields);
            $valueOperators = array_keys(self::$VALUE_OPERATORS);
            $nullOperators = array_keys(self::$NULL_OPERATORS);
            for($i = 0; $i < $size; $i++) {
                $field = $fields[$i];
                $value = $arguments[$i] ?? null;
                $operand =  $operators[$i] ?? null;
                if (in_array($operand, self::$PRIMARY_OPERATORS)) {
                    $whereConditions[] = WhereCondition::compose($field, $operand, $value);
                } elseif (in_array($operand, $valueOperators)) {
                    $whereConditions[] = WhereCondition::compose($field, self::$VALUE_OPERATORS[$operand], $value);
                } elseif (in_array($operand, $nullOperators)) {
                    $whereConditions[] = WhereCondition::compose($field, self::$NULL_OPERATORS[$operand], null);
                } else {
                    $whereConditions[] = WhereCondition::compose($field, is_array($value) ? "IN" : "=", $value);
                }
                if (in_array($operand, self::$JOINT_OPERATORS)) {
                    $jointOperators[] = $operand;
                }
            }

            //Concat / Joint Operands => [And, Or]
            $size = count($jointOperators);
            if ($size > 0) {
                $compositeCondition = null;
                $composed = false;
                for($i = 0; $i < $size; $i++) {
                    $concatOperand = $jointOperators[$i];
                    $nextCondition = $composed ? 
                                    [$compositeCondition, $whereConditions[$i+1]] : 
                                    [$whereConditions[$i], $whereConditions[$i+1]];
                    $compositeCondition = WhereCompositeCondition::compose(
                        $nextCondition,
                        strtoupper($concatOperand)
                    );
                    $composed = true;
                }
                return $compositeCondition;
            } else {
                return $whereConditions;
            }
        }
    }



}