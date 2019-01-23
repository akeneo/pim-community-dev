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

namespace Akeneo\Pim\Automation\FranklinInsights\Infrastructure\InternalApi\Normalizer;

use Akeneo\Pim\Automation\FranklinInsights\Domain\IdentifierMapping\Model\IdentifiersMapping;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class IdentifiersMappingNormalizer
{
    /**
     * @param IdentifiersMapping $identifiersMapping
     *
     * @return array
     */
    public function normalize(IdentifiersMapping $identifiersMapping): array
    {
        $normalizedMapping = [];

        foreach ($identifiersMapping->getMapping() as $franklinIdentifierCode => $identifier) {
            $normalizedMapping[$franklinIdentifierCode] = null;
            $attribute = $identifier->getAttribute();
            if (null !== $attribute) {
                $normalizedMapping[$franklinIdentifierCode] = $attribute->getCode();
            }
        }

        return $normalizedMapping;
    }
}
