<?php

namespace Emma\ORM\Repository\Helpers;

trait OperatorHelper
{
    protected const SUPPORTED_OPERATORS = "And|Or";
    //TODO: version2 -> operator to support --> Is[param_value], IsNull, IsNotNull, IsNot[param_value], etc...

    /**
     * @param string $parameterString
     * @return array
     */
    protected function getOperators(string $parameterString): array
    {
        $operators = [];
        $regex = "/(" . $parameterString . ")/";
        if (preg_match_all($regex, self::SUPPORTED_OPERATORS, $operators) !== false) {
            return $operators[0];
        }
        return $operators;
    }

}