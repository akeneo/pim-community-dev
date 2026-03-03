<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\PublicApi\Query\AttributeGroup;

use Akeneo\Pim\Automation\DataQualityInsights\PublicApi\Model\AttributeGroupsActivation;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface GetAttributeGroupsActivationQueryInterface
{
    public function all(): AttributeGroupsActivation;
}
