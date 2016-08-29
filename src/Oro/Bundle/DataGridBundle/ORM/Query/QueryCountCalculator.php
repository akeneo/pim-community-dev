<?php

namespace Oro\Bundle\DataGridBundle\ORM\Query;

use Doctrine\ORM\Query;
use Doctrine\ORM\Query\Parameter;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\QueryException;

/**
 * Calculates total count of query records
 */
class QueryCountCalculator
{
    /**
     * Calculates total count of query records
     *
     * @param Query $query
     * @param \Doctrine\Common\Collections\ArrayCollection|array|null $parameters Query parameters.
     * @return integer
     */
    public static function calculateCount(Query $query, $parameters = null)
    {
        /** @var QueryCountCalculator $instance */
        $instance = new static();
        return $instance->getCount($query, $parameters);
    }

    /**
     * Calculates total count of query records
     *
     * @param Query $query
     * @param \Doctrine\Common\Collections\ArrayCollection|array|null $parameters Query parameters.
     * @return integer
     */
    public function getCount(Query $query, $parameters = null)
    {
        if (!empty($parameters)) {
            $query = clone $query;
            $query->setParameters($parameters);
        }
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

        return $result ? (int) $result : 0;
    }

    /**
     * @param Query                              $query
     * @param array                              $paramMappings
     * @throws \Doctrine\ORM\Query\QueryException
     * @return array
     */
    protected function processParameterMappings(Query $query, $paramMappings)
    {
        $sqlParams = [];
        $types = [];

        /** @var Parameter $parameter */
        foreach ($query->getParameters() as $parameter) {
            $key = $parameter->getName();

            if (!isset($paramMappings[$key])) {
                throw QueryException::unknownParameter($key);
            }

            $value = $query->processParameterValue($parameter->getValue());
            $type = ($parameter->getValue() === $value)
                ? $parameter->getType()
                : Query\ParameterTypeInferer::inferType($value);

            foreach ($paramMappings[$key] as $position) {
                $types[$position] = $type;
            }

            $sqlPositions = $paramMappings[$key];
            $value = [$value];
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

        return [$sqlParams, $types];
    }
}
