<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\PublicApi\Model;

use Akeneo\Pim\Automation\DataQualityInsights\PublicApi\Model\QualityScore;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class AttributeGroupsActivationSpec extends ObjectBehavior
{
    public function it_says_if_an_attribute_group_is_activated(): void
    {
        $rawAttributeGroupsActivation = [
            'an_attribute_group' => true,
            'another_attribute_group' => false,
        ];

        $this->beConstructedWith($rawAttributeGroupsActivation);

        $this->isActivated('an_attribute_group')->shouldReturn(true);
        $this->isActivated('another_attribute_group')->shouldReturn(false);
    }
}
