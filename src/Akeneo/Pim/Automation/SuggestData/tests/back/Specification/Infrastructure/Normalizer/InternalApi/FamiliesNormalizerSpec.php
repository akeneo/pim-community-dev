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

namespace Specification\Akeneo\Pim\Automation\SuggestData\Infrastructure\Normalizer\InternalApi;

use Akeneo\Pim\Automation\SuggestData\Domain\Model\Read\Family;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\Read\FamilyCollection;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Normalizer\InternalApi\FamiliesNormalizer;
use PhpSpec\ObjectBehavior;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class FamiliesNormalizerSpec extends ObjectBehavior
{
    public function it_is_a_families_normalizer()
    {
        $this->shouldBeAnInstanceOf(FamiliesNormalizer::class);
    }

    public function it_normalizes_families()
    {
        $familyCollection = new FamilyCollection();
        $familyCollection
            ->add(new Family('router', ['en_US' => 'router', 'fr_FR' => 'routeur']))
            ->add(new Family('camcorders', ['en_US' => 'camcorders']));

        $expectedFamilies = [
            [
                'code' => 'router',
                'status' => 0,
                'labels' => [
                    'en_US' => 'router',
                    'fr_FR' => 'routeur',
                ]
            ],
            [
                'code' => 'camcorders',
                'status' => 0,
                'labels' => [
                    'en_US' => 'camcorders',
                ]
            ],
        ];

        $this->normalize($familyCollection)->shouldReturn($expectedFamilies);
    }
}
