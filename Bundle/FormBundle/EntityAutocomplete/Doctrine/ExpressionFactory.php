<?php

namespace Oro\Bundle\FormBundle\EntityAutocomplete\Doctrine;

use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;

class ExpressionFactory extends Expr
{
    /**
     * Creates concat expression with multiple arguments
     *
     * @param array $parts
     * @param string|null $joinLiteral
     * @return string|Expr\Base
     * @throws \InvalidArgumentException When less then 2 fields
     */
    public function multipleConcat(array $parts, $joinLiteral = null)
    {
        if (count($parts) < 2) {
            throw new \InvalidArgumentException('$fields contain be less then 2 elements.');
        }
        $concatArguments = array();
        foreach ($parts as $expr) {
            if ($concatArguments && $joinLiteral) {
                $concatArguments[] = $this->literal($joinLiteral);
            }
            $concatArguments[] = $expr;
        }
        $result = $this->concat($concatArguments[0], $concatArguments[1]);
        foreach (array_slice($concatArguments, 2) as $argument) {
            $result = $this->concat($result, $argument);
        }
        return $result;
    }
}
