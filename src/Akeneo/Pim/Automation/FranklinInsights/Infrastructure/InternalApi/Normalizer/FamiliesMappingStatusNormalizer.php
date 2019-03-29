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

use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeMapping\Model\Read\FamilyMappingStatusCollection;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class FamiliesMappingStatusNormalizer
{
    /**
     * @param FamilyMappingStatusCollection $familyCollection
     *
     * @return array
     */
    public function normalize(FamilyMappingStatusCollection $familyCollection): array
    {
        $families = [];

        foreach ($familyCollection as $familyMappingStatus) {
            $families[] = [
                'code' => (string) $familyMappingStatus->getFamily()->getCode(),
                'status' => $familyMappingStatus->getMappingStatus(),
                'labels' => $familyMappingStatus->getFamily()->getLabels(),
            ];
        }

        return $families;
    }
}
