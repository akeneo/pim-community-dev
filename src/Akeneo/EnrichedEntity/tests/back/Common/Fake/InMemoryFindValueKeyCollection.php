<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\EnrichedEntity\tests\back\Common\Fake;

use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;
use Akeneo\EnrichedEntity\Domain\Query\Attribute\FindValueKeyCollectionInterface;

/**
 * @author Julien Sanchez <julien@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class InMemoryFindValueKeyCollection implements FindValueKeyCollectionInterface
{
    /**
     * {@inheritdoc}
     */
    public function __invoke(EnrichedEntityIdentifier $identifier): array
    {
        throw \NotImplementedException();
    }
}
