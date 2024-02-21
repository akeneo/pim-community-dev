<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Model;

use Akeneo\Pim\Enrichment\Component\Product\Model\Group;
use Akeneo\Pim\Enrichment\Component\Product\Model\GroupTranslation;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GroupSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType(Group::class);
    }

    public function it_gets_a_translation_even_if_the_locale_case_is_wrong(
        GroupTranslation $translationEn,
    )
    {
        $translationEn->getLocale()->willReturn('EN_US');
        $this->addTranslation($translationEn);

        $this->getTranslation('en_US')->shouldReturn($translationEn);
    }
}
