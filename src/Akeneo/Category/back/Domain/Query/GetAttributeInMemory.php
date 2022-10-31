<?php

declare(strict_types=1);

namespace Akeneo\Category\Domain\Query;

use Akeneo\Category\Domain\ValueObject\Attribute\AttributeCollection;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 *
 * @phpstan-type Identifier string
 */
interface GetAttributeInMemory
{
    /**
     * @param array<Identifier> $identifiers {example : [title|1234579-1354]}
     */
    public function byIdentifiers(array $identifiers): AttributeCollection;
}
