<?php

namespace Emma\ORM\Repository\Helpers;

use Emma\Di\Utils\StringManagement;

trait ParameterHelper
{
    protected string $parameterString;

    public const SUPPORTED_METHODS = [
        "findBy", "findOneBy", "findArrayBy", "findOneArrayBy", "countBy", "existsBy", "deleteBy", "deleteAllBy"
    ];

    /**
     * @param $methodName
     * @return string
     */
    protected function getParameterString($methodName): string
    {
        $this->parameterString = str_replace(self::SUPPORTED_METHODS, "", $methodName);
        return $this->parameterString;
    }

    /**
     * @param array $entityPropertyNames
     * @return string
     */
    protected function stringifyPropertyNames(array $entityPropertyNames): string
    {
        foreach($entityPropertyNames as $index => $name) {
            $entityPropertyNames[$index] = StringManagement::underscoreToCamelCase($name);
        }
        return implode("|", $entityPropertyNames);
    }

    /**
     * @param array $entityPropertyNames
     * @param $methodName
     * @return array
     */
    protected function getParameterNames(array $entityPropertyNames, $methodName): array
    {
        $parameterString = $this->getParameterString($methodName);
        $fieldNames = [];
        $regex = "/(" . $this->stringifyPropertyNames($entityPropertyNames) . ")/i";
        if (preg_match_all($regex, $parameterString, $fieldNames) !== false) {
            $result = [];
            $entityPropertyNamesLowerCase = array_map('strtolower', $entityPropertyNames);
            foreach($fieldNames[0] as $propertyNames) {
                if (false !== $columName = array_search(strtolower($propertyNames), $entityPropertyNamesLowerCase)) {
                    $result[] = $columName;
                }
            }
            return $result;
        }

        $columnNames = array_keys($entityPropertyNames);
        $regex = "/(" . $this->stringifyPropertyNames($columnNames) . ")/i";
        if (preg_match_all($regex, $parameterString, $fieldNames) !== false) {
            return $fieldNames[0];
        }
        return $fieldNames;
    }

}