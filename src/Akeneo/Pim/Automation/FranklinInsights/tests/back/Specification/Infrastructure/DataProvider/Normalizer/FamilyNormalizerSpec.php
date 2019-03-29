<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Infrastructure\DataProvider\Normalizer;

use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\Model\Read\Family;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\FamilyCode;
use PhpSpec\ObjectBehavior;

class FamilyNormalizerSpec extends ObjectBehavior
{
    public function it_normalizes_a_family_to_a_franklin_array_format(): void
    {
        $family = new Family(
            new FamilyCode('tshirt'),
            [
                'fr_FR' => 'T-shirt',
                'en_US' => 'T-shirt',
            ]
        );

        $this->normalize($family)->shouldReturn([
            'code' => 'tshirt',
            'label' => [
                'fr_FR' => 'T-shirt',
                'en_US' => 'T-shirt',
            ],
        ]);
    }
}
