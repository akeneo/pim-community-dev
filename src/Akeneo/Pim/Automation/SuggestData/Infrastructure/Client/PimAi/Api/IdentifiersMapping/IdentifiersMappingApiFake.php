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

/**
 * Fake identifiers mapping API
 *
 * @author Pierre Allard <pierre.allard@akeneo.com>
 */
class IdentifiersMappingApiFake implements IdentifiersMappingApiInterface
{
    /**
     * {@inheritdoc}
     */
    public function update(array $mapping): void
    {
    }
}
