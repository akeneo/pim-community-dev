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

use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeMapping\Model\Read\FamilyCollection;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class FamiliesNormalizer
{
    /**
     * @param FamilyCollection $familyCollection
     *
     * @return array
     */
    public function normalize(FamilyCollection $familyCollection): array
    {
        $families = [];

        foreach ($familyCollection as $family) {
            $families[] = [
                'code' => $family->getCode(),
                'status' => $family->getMappingStatus(),
                'labels' => $family->getLabels(),
            ];
        }

        return $families;
    }
}
