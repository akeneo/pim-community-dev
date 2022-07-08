<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\API\Query;

use Ramsey\Uuid\UuidInterface;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @extends \Iterator<UuidInterface|false>
 */
interface ProductUuidCursorInterface extends \Countable, \Iterator
{
}
