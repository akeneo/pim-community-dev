<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Akeneo\Platform\Bundle\FrameworkBundle\Logging;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ControllerResolverInterface;

/**
 * @author JM Leroux <jean-marie.leroux@akeneo.com>
 */
class BoundedContextResolver
{
    public function __construct(
        private ControllerResolverInterface $controllerResolver,
        private array $boundedContexts
    ) {
    }

    public function fromRequest(Request $request): string
    {
        // we need to check for the attribute to avoid useless log triggered by controller resolver
        // @link https://github.com/symfony/symfony/blob/5.2/src/Symfony/Component/HttpKernel/Controller/ControllerResolver.php#L40
        if (!$request->attributes->has('_controller')) {
            return 'Unknown request context: no controller in request';
        }

        try {
            $controller = $this->controllerResolver->getController($request);
        } catch (\Error) {
            return 'Unknown request context: unable to instantiate the controller';
        }

        if (false === $controller) {
            return 'Unknown request context: no controller in request';
        }

        $namespace = is_array($controller) ? get_class($controller[0]) : get_class($controller);

        return $this->findContext($namespace) ?: sprintf(
            'Unknown namespace context: %s',
            str_replace('\\', '\\\\', $namespace));
    }

    private function findContext(string $nameSpace): ?string
    {
        foreach ($this->boundedContexts as $namespaceStart => $boundedContext) {
            if (str_starts_with($nameSpace, $namespaceStart)) {
                return $boundedContext;
            }
        }

        return null;
    }

    public function fromCommand(Command $command): ?string
    {
        return $this->findContext(get_class($command));
    }
}
