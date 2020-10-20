<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Symfony\Controller;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\AttributeGroupActivation;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Repository\AttributeGroupActivationRepositoryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AttributeGroupCode;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class UpdateAttributeGroupActivationController
{
    /** @var AttributeGroupActivationRepositoryInterface */
    private $attributeGroupActivationRepository;

    /** @var SecurityFacade */
    private $securityFacade;

    public function __construct(
        AttributeGroupActivationRepositoryInterface $attributeGroupActivationRepository,
        SecurityFacade $securityFacade
    ) {
        $this->attributeGroupActivationRepository = $attributeGroupActivationRepository;
        $this->securityFacade = $securityFacade;
    }

    public function __invoke(Request $request)
    {
        if (!$this->securityFacade->isGranted('akeneo_data_quality_insights_activation_attribute_group_edit')) {
            throw new AccessDeniedException();
        }

        try {
            $attributeGroupCode = new AttributeGroupCode($request->request->get('attribute_group_code'));
            $activated = $request->request->getBoolean('activated');
            $attributeGroupActivation = new AttributeGroupActivation($attributeGroupCode, $activated);
        } catch (\Throwable $e) {
            return new Response(null, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $this->attributeGroupActivationRepository->save($attributeGroupActivation);

        return new Response(null, Response::HTTP_NO_CONTENT);
    }
}
