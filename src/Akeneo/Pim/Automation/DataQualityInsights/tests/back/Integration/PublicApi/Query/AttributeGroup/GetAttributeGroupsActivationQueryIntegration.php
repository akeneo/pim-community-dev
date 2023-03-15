<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\DataQualityInsights\Integration\Infrastructure\PublicApi\Query\AttributeGroup;

use Akeneo\Pim\Automation\DataQualityInsights\PublicApi\Model\AttributeGroupsActivation;
use Akeneo\Test\Pim\Automation\DataQualityInsights\Integration\DataQualityInsightsTestCase;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class GetAttributeGroupsActivationQueryIntegration extends DataQualityInsightsTestCase
{
    public function test_it_returns_attribute_groups_activation(): void
    {
        $this->createAttributeGroup('an_attribute_group');
        $this->createAttributeGroupActivation('an_attribute_group');
        $this->createAttributeGroup('another_attribute_group');
        $this->createAttributeGroupActivation('another_attribute_group', false);

        $expectedAttributeGroupsActivation = new AttributeGroupsActivation([
            'an_attribute_group' => true,
            'another_attribute_group' => false,
            'other' => true,
        ]);

        $query = $this->get('Akeneo\Pim\Automation\DataQualityInsights\PublicApi\Query\AttributeGroup\GetAttributeGroupsActivationQuery');
        $actualAttributeGroupsActivation = $query->all();

        $this->assertEquals($expectedAttributeGroupsActivation, $actualAttributeGroupsActivation);
    }
}
