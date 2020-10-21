<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Symfony\Controller;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Structure\GetAllAttributeGroupsActivationQueryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetAllAttributeGroupsActivationController
{
    /** @var GetAllAttributeGroupsActivationQueryInterface */
    private $getAllAttributeGroupsActivationQuery;

    public function __construct(GetAllAttributeGroupsActivationQueryInterface $getAllAttributeGroupsActivationQuery)
    {
        $this->getAllAttributeGroupsActivationQuery = $getAllAttributeGroupsActivationQuery;
    }

    public function __invoke(): JsonResponse
    {
        $attributeGroupActivation = $this->getAllAttributeGroupsActivationQuery->execute();

        return new JsonResponse($attributeGroupActivation);
    }
}
