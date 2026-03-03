<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Controller;

use Akeneo\Pim\Automation\IdentifierGenerator\Application\Update\ReorderGeneratorsCommand;
use Akeneo\Pim\Automation\IdentifierGenerator\Application\Update\ReorderGeneratorsHandler;
use Akeneo\Platform\Bundle\FrameworkBundle\Security\SecurityFacadeInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Webmozart\Assert\Assert;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ReorderIdentifierGeneratorsController
{
    public function __construct(
        private readonly ReorderGeneratorsHandler $handler,
        private readonly SecurityFacadeInterface $security,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        if (!$this->security->isGranted('pim_identifier_generator_manage')) {
            throw new AccessDeniedException();
        }

        $orderedCodes = $request->get('codes', []);
        Assert::isArray($orderedCodes);
        Assert::allStringNotEmpty($orderedCodes);
        ($this->handler)(ReorderGeneratorsCommand::fromCodes($orderedCodes));

        return new JsonResponse();
    }
}
