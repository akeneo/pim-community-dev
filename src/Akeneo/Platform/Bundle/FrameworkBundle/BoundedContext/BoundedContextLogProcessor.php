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

use Symfony\Component\HttpFoundation\RequestStack;

/**
 * @author JM Leroux <jean-marie.leroux@akeneo.com>
 */
class BoundedContextLogProcessor
{
    private RequestStack $requestStack;
    private BoundedContextResolver $boundedContextResolver;

    public function __construct(RequestStack $requestStack, BoundedContextResolver $boundedContextResolver)
    {
        $this->requestStack = $requestStack;
        $this->boundedContextResolver = $boundedContextResolver;
    }

    public function __invoke(array $record): array
    {
        try {
            $request = $this->requestStack->getMasterRequest();

            if (null === $request) {
                return $record;
            }

            $logContext = $record['context'] ?? [];

            $url = 'Unable to guess URL from request';
            if ($request->getSchemeAndHttpHost() && $request->getPathInfo()) {
                $url = sprintf('%s%s', $request->getSchemeAndHttpHost(), $request->getPathInfo());
            }

            $logContext['akeneo_context'] = $this->boundedContextResolver->fromRequest($request);
            $logContext['path_info'] = $url;

            $record['context'] = $logContext;
        } catch (\Exception $e) {
            return $record;
        }

        return $record;
    }
}
