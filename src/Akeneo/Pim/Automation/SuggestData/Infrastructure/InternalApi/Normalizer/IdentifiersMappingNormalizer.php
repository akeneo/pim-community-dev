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

namespace Akeneo\Pim\Automation\SuggestData\Infrastructure\InternalApi\Normalizer;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class IdentifiersMappingNormalizer
{
    /**
     * @param array $identifiers
     *
     * @return array
     */
    public function normalize(array $identifiers): array
    {
        $normalizedData = [];
        foreach ($identifiers as $identifier => $attribute) {
            $normalizedData[$identifier] = (null !== $attribute) ? $attribute->getCode() : null;
        }

        return $normalizedData;
    }
}
