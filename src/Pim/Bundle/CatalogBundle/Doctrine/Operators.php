<?php

namespace Pim\Bundle\CatalogBundle\Doctrine;

final class Operators
{
    const STARTS_WITH           = 'STARTS WITH';
    const ENDS_WITH             = 'ENDS WITH';
    const CONTAINS              = 'CONTAINS';
    const DOES_NOT_CONTAIN      = 'DOES NOT CONTAIN';
    const IS_EMPTY              = 'EMPTY';
    const IN_LIST               = 'IN';
    const NOT_IN_LIST           = 'NOT IN';
    const BETWEEN               = 'BETWEEN';
    const NOT_BETWEEN           = 'NOT BETWEEN';
    const GREATER_THAN          = '>';
    const GREATER_OR_EQUAL_THAN = '>=';
    const LOWER_THAN            = '<';
    const LOWER_OR_EQUAL_THAN   = '<=';
    const EQUALS                = '=';
}
