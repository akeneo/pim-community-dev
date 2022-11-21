<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Symfony\Controller;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Structure\GetAttributeGroupActivationQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AttributeGroupCode;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetAttributeGroupActivationController
{
    /** @var GetAttributeGroupActivationQueryInterface */
    private $getAttributeGroupActivationQuery;

    public function __construct(GetAttributeGroupActivationQueryInterface $getAttributeGroupActivationQuery)
    {
        $this->getAttributeGroupActivationQuery = $getAttributeGroupActivationQuery;
    }

    public function __invoke(string $attributeGroupCode): JsonResponse
    {
        try {
            $attributeGroupCode = new AttributeGroupCode($attributeGroupCode);
        } catch (\InvalidArgumentException $exception) {
            return new JsonResponse(['message' => $exception->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        $attributeGroupActivation = $this->getAttributeGroupActivationQuery->byCode($attributeGroupCode);

        if (null === $attributeGroupActivation) {
            return new JsonResponse(null, Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse([
            'attribute_group_code' => strval($attributeGroupActivation->getAttributeGroupCode()),
            'activated' => $attributeGroupActivation->isActivated(),
        ]);
    }
}
