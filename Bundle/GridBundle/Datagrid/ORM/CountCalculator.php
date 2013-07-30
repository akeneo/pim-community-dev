<?php

namespace Oro\Bundle\GridBundle\Datagrid\ORM;

use Doctrine\ORM\Query;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\Parameter;
use Doctrine\ORM\Query\QueryException;

class CountCalculator
{
    /**
     * @param Query $query
     * @return int
     */
    public function getCount(Query $query)
    {
        $parser = new Parser($query);
        $parserResult = $parser->parse();
        $parameterMappings = $parserResult->getParameterMappings();
        list($sqlParameters, $parameterTypes) = $this->processParameterMappings($query, $parameterMappings);

        $statement = $query->getEntityManager()->getConnection()->executeQuery(
            'SELECT COUNT(*) FROM (' . $query->getSQL() .') AS e',
            $sqlParameters,
            $parameterTypes
        );
        $result = $statement->fetchColumn();

        return $result ? (int)$result : 0;
    }

    /**
     * @param Query $query
     * @param array $paramMappings
     * @return array
     * @throws \Doctrine\ORM\Query\QueryException
     */
    protected function processParameterMappings(Query $query, $paramMappings)
    {
        $sqlParams = array();
        $types     = array();

        /** @var Parameter $parameter */
        foreach ($query->getParameters() as $parameter) {
            $key = $parameter->getName();

            if (!isset($paramMappings[$key])) {
                throw QueryException::unknownParameter($key);
            }

            $value = $query->processParameterValue($parameter->getValue());
            $type  = ($parameter->getValue() === $value)
                ? $parameter->getType()
                : Query\ParameterTypeInferer::inferType($value);

            foreach ($paramMappings[$key] as $position) {
                $types[$position] = $type;
            }

            $sqlPositions = $paramMappings[$key];
            $value = array($value);
            $countValue = count($value);

            for ($i = 0, $l = count($sqlPositions); $i < $l; $i++) {
                $sqlParams[$sqlPositions[$i]] = $value[($i % $countValue)];
            }
        }

        if (count($sqlParams) != count($types)) {
            throw QueryException::parameterTypeMissmatch();
        }

        if ($sqlParams) {
            ksort($sqlParams);
            $sqlParams = array_values($sqlParams);

            ksort($types);
            $types = array_values($types);
        }

        return array($sqlParams, $types);
    }
}
