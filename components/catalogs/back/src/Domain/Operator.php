<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Domain;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class Operator
{
    public const IS_EMPTY = 'EMPTY';
    public const IS_NOT_EMPTY = 'NOT EMPTY';
    public const IN_LIST = 'IN';
    public const NOT_IN_LIST = 'NOT IN';
    public const STARTS_WITH = 'STARTS WITH';
    public const CONTAINS = 'CONTAINS';
    public const DOES_NOT_CONTAIN = 'DOES NOT CONTAIN';
    public const EQUALS = '=';
    public const NOT_EQUAL = '!=';
    public const IN_CHILDREN_LIST = 'IN CHILDREN';
    public const NOT_IN_CHILDREN_LIST = 'NOT IN CHILDREN';
    public const UNCLASSIFIED = 'UNCLASSIFIED';
    public const IN_LIST_OR_UNCLASSIFIED = 'IN OR UNCLASSIFIED';
    public const LOWER_THAN = '<';
    public const LOWER_OR_EQUAL_THAN = '<=';
    public const GREATER_THAN = '>';
    public const GREATER_OR_EQUAL_THAN = '>=';
    public const BETWEEN = 'BETWEEN';
    public const NOT_BETWEEN = 'NOT BETWEEN';
}
