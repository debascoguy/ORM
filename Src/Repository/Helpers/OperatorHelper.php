<?php

namespace Emma\ORM\Repository\Helpers;

trait OperatorHelper
{
    protected const SUPPORTED_OPERATORS = "And|Or|IsNotNull|IsNull|IsNot|NotIn|Is\(|In\(";
    
    protected static array $JOINT_OPERATORS = ["And", "Or"];

    protected static array $PRIMARY_OPERATORS = ["IN", "=", "!=", "<", ">"];

    protected static array $NULL_OPERATORS = ["IsNotNull" => "IS NOT", "IsNull" => "IS"];

    protected static array $VALUE_OPERATORS = ["IsNot" => "!=", "NotIn" => "NOT IN", "Is(" => "=", "Is" => "=", "In(" => "IN", "In" => "IN"];

    /**
     * @param string $parameterString
     * @return array
     */
    protected function getOperators(string $parameterString): array
    {
        $operators = [];
        $regex = "/(" . self::SUPPORTED_OPERATORS . ")/";
        if (preg_match_all($regex, $parameterString."(", $operators) !== false) {
            return $operators[0];
        }
        return $operators;
    }

}