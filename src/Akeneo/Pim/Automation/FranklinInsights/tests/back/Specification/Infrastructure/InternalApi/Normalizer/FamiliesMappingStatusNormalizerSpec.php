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

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Infrastructure\InternalApi\Normalizer;

use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeMapping\Model\Read\FamilyMappingStatus;
use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeMapping\Model\Read\FamilyMappingStatusCollection;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\Model\Read\Family;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\FamilyCode;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\InternalApi\Normalizer\FamiliesMappingStatusNormalizer;
use PhpSpec\ObjectBehavior;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class FamiliesMappingStatusNormalizerSpec extends ObjectBehavior
{
    public function it_is_a_families_normalizer(): void
    {
        $this->shouldBeAnInstanceOf(FamiliesMappingStatusNormalizer::class);
    }

    public function it_normalizes_families(): void
    {
        $familyCollection = new FamilyMappingStatusCollection();
        $familyCollection
            ->add(new FamilyMappingStatus(
                new Family(new FamilyCode('router'), ['en_US' => 'router', 'fr_FR' => 'routeur']),
                FamilyMappingStatus::MAPPING_PENDING
            ))
            ->add(new FamilyMappingStatus(
                new Family(new FamilyCode('camcorders'), ['en_US' => 'camcorders']),
                FamilyMappingStatus::MAPPING_PENDING
            ));

        $expectedFamilies = [
            [
                'code' => 'router',
                'status' => 0,
                'labels' => [
                    'en_US' => 'router',
                    'fr_FR' => 'routeur',
                ],
            ],
            [
                'code' => 'camcorders',
                'status' => 0,
                'labels' => [
                    'en_US' => 'camcorders',
                ],
            ],
        ];

        $this->normalize($familyCollection)->shouldReturn($expectedFamilies);
    }
}
