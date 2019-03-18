<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Domain\Common\Model\Read;

use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\Model\Read\Family;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\FamilyCode;
use PhpSpec\ObjectBehavior;

class FamilySpec extends ObjectBehavior
{
    public function let(): void
    {
        $this->beConstructedWith(
            new FamilyCode('router'),
            [
                'en_US' => 'Router',
                'fr_FR' => 'Routeur',
            ]
        );
    }

    public function it_is_a_family_read_model(): void
    {
        $this->shouldHaveType(Family::class);
    }

    public function it_gets_the_family_code(): void
    {
        $familyCode = $this->getCode();
        $familyCode->shouldBeLike(new FamilyCode('router'));
    }

    public function it_gets_the_labels(): void
    {
        $this->getLabels()->shouldReturn([
            'en_US' => 'Router',
            'fr_FR' => 'Routeur',
        ]);
    }

    public function it_gets_the_label_for_a_given_locale()
    {
        $this->getLabel('en_US')->shouldReturn('Router');
    }

    public function it_gets_the_code_as_default_label_if_there_is_no_label_for_a_given_locale()
    {
        $this->getLabel('en_AU')->shouldReturn('[router]');
    }
}
