<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Symfony\Controller;

use Akeneo\Pim\Automation\DataQualityInsights\Application\Command\UpdateAttributeGroupActivationCommand;
use Akeneo\Pim\Automation\DataQualityInsights\Application\Command\UpdateAttributeGroupActivationHandler;
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
    public function __construct(
        private readonly SecurityFacade $securityFacade,
        private readonly UpdateAttributeGroupActivationHandler $attributeGroupActivationHandler
    ) {
    }

    public function __invoke(Request $request)
    {
        if (!$this->securityFacade->isGranted('akeneo_data_quality_insights_activation_attribute_group_edit')) {
            throw new AccessDeniedException();
        }

        try {
            $command = new UpdateAttributeGroupActivationCommand(
                $request->request->get('attribute_group_code'),
                $request->request->getBoolean('activated')
            );
        } catch (\Throwable) {
            return new Response(null, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        ($this->attributeGroupActivationHandler)($command);

        return new Response(null, Response::HTTP_NO_CONTENT);
    }
}
