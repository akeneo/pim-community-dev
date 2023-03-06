<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Condition;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
enum CategoryOperator: string
{
    case IN = 'IN';
    case NOT_IN = 'NOT IN';
    case IN_CHILDREN_LIST = 'IN CHILDREN';
    case NOT_IN_CHILDREN_LIST = 'NOT IN CHILDREN';
    case CLASSIFIED = 'CLASSIFIED';
    case UNCLASSIFIED = 'UNCLASSIFIED';
}
