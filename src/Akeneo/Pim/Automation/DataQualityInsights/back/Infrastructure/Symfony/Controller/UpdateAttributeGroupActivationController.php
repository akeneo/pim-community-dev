<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Symfony\Controller;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\AttributeGroupActivation;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Repository\AttributeGroupActivationRepositoryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AttributeGroupCode;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class UpdateAttributeGroupActivationController
{
    /** @var AttributeGroupActivationRepositoryInterface */
    private $attributeGroupActivationRepository;

    public function __construct(AttributeGroupActivationRepositoryInterface $attributeGroupActivationRepository)
    {
        $this->attributeGroupActivationRepository = $attributeGroupActivationRepository;
    }

    public function __invoke(Request $request)
    {
        try {
            $attributeGroupCode = new AttributeGroupCode($request->request->get('attribute_group_code'));
            $activated = $request->request->get('activated');
            $attributeGroupActivation = new AttributeGroupActivation($attributeGroupCode, $activated);
        } catch (\Throwable $e) {
            return new Response(null, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $this->attributeGroupActivationRepository->save($attributeGroupActivation);

        return new Response(null, Response::HTTP_NO_CONTENT);
    }
}
