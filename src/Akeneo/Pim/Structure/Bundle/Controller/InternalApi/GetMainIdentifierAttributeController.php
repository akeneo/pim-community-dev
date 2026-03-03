<?php

namespace Akeneo\Pim\Structure\Bundle\Controller\InternalApi;

use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\UserManagement\Bundle\Context\UserContext;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetMainIdentifierAttributeController
{
    public function __construct(
        private readonly AttributeRepositoryInterface $attributeRepository,
        private readonly NormalizerInterface $normalizer,
        private readonly UserContext $userContext,
    ) {
    }

    public function __invoke(): JsonResponse
    {
        $attribute = $this->attributeRepository->getMainIdentifier();

        return new JsonResponse(
            $this->normalizer->normalize(
                $attribute,
                'internal_api',
                ['locale' => $this->userContext->getUiLocale()->getCode()]
            )
        );
    }
}
