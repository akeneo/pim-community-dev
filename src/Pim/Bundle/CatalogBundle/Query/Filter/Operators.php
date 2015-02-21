<?php

namespace Pim\Bundle\CatalogBundle\Query\Filter;

/**
 * Filter operators
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Operators
{
    const STARTS_WITH             = 'STARTS WITH';
    const ENDS_WITH               = 'ENDS WITH';
    const CONTAINS                = 'CONTAINS';
    const DOES_NOT_CONTAIN        = 'DOES NOT CONTAIN';
    const IS_EMPTY                = 'EMPTY';
    const IN_LIST                 = 'IN';
    const NOT_IN_LIST             = 'NOT IN';
    const IN_CHILDREN_LIST        = 'IN CHILDREN';
    const NOT_IN_CHILDREN_LIST    = 'NOT IN CHILDREN';
    const UNCLASSIFIED            = 'UNCLASSIFIED';
    const IN_LIST_OR_UNCLASSIFIED = 'IN OR UNCLASSIFIED';
    const BETWEEN                 = 'BETWEEN';
    const NOT_BETWEEN             = 'NOT BETWEEN';
    const GREATER_THAN            = '>';
    const GREATER_OR_EQUAL_THAN   = '>=';
    const LOWER_THAN              = '<';
    const LOWER_OR_EQUAL_THAN     = '<=';
    const EQUALS                  = '=';
}
