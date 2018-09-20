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

namespace Akeneo\EnrichedEntity\Common\Fake;

use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;
use Akeneo\EnrichedEntity\Domain\Query\Record\GenerateEmptyValuesInterface;
use Akeneo\Test\Acceptance\Common\NotImplementedException;

/**
 * @author Julien Sanchez <julien@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class InMemoryGenerateEmptyValues implements GenerateEmptyValuesInterface
{
    /**
     * {@inheritdoc}
     */
    public function __invoke(EnrichedEntityIdentifier $identifier): array
    {
        throw new NotImplementedException('__invoke');
    }
}
