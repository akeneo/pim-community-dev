<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\Api\IdentifiersMapping;

use Akeneo\Pim\Automation\SuggestData\Domain\Model\IdentifiersMapping;

/**
 * Fake identifiers mapping updater
 *
 * @author Pierre Allard <pierre.allard@akeneo.com>
 */
class IdentifiersMappingFake implements IdentifiersMappingInterface
{
    /**
     * {@inheritdoc}
     */
    public function update(IdentifiersMapping $mapping): void
    {
    }
}
