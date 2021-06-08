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

namespace Akeneo\Platform\Bundle\FrameworkBundle\BoundedContext;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ControllerResolverInterface;

/**
 * @author JM Leroux <jean-marie.leroux@akeneo.com>
 */
class BoundedContextResolver
{
    private ControllerResolverInterface $controllerResolver;
    /** @var string[] */
    private array $boundedContexts;

    public function __construct(ControllerResolverInterface $controllerResolver, array $boundedContexts)
    {
        $this->controllerResolver = $controllerResolver;
        $this->boundedContexts = $boundedContexts;
    }

    public function fromRequest(Request $request): string
    {
        $controller = $this->controllerResolver->getController($request);

        if (false === $controller) {
            return 'Unknown request context';
        }

        $namespace = is_array($controller) ? get_class($controller[0]) : get_class($controller);

        foreach ($this->boundedContexts as $namespaceStart => $boundedContext) {
            if (strpos($namespace, $namespaceStart) === 0) {
                return $boundedContext;
            }
        }

        return sprintf('Unknown namespace context: %s', str_replace('\\', '\\\\', $namespace));
    }
}
