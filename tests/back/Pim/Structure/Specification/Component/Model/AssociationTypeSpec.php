<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Structure\Component\Model;

use Akeneo\Pim\Structure\Component\Model\AssociationType;
use Akeneo\Pim\Structure\Component\Model\AssociationTypeTranslation;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AssociationTypeSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType(AssociationType::class);
    }

    public function it_gets_a_translation_even_if_the_locale_case_is_wrong(
        AssociationTypeTranslation $translationEn,
    )
    {
        $translationEn->getLocale()->willReturn('EN_US');
        $this->addTranslation($translationEn);

        $this->getTranslation('en_US')->shouldReturn($translationEn);
    }
}
