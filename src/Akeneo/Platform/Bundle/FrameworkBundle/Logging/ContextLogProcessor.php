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

use Ramsey\Uuid\Uuid;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * @author JM Leroux <jean-marie.leroux@akeneo.com>
 */
class ContextLogProcessor
{
    private array $cachedContext = [];
    private ?string $traceId = null;

    public function __construct(
        private RequestStack $requestStack,
        private BoundedContextResolver $boundedContextResolver
    ) {
    }

    public function __invoke(array $record): array
    {
        if (empty($this->cachedContext)) {
            if ($request = $this->requestStack->getMainRequest()) {
                $this->cachedContext['path_info'] = sprintf(
                    '%s%s',
                    $request->getSchemeAndHttpHost(),
                    $request->getPathInfo()
                );
                $this->cachedContext['akeneo_context'] = $this->boundedContextResolver->fromRequest($request);
                $this->traceId = $request->headers->get('X-Datadog-Trace-Id') ?: $request->headers->get('trace-id');
            }
            $this->traceId = $this->traceId ?: (string) Uuid::uuid4();
        }

        $record['context'] = array_merge($record['context'] ?? [], $this->cachedContext);
        $record['trace_id'] = $this->traceId;

        return $record;
    }

    public function initCommandContext(Command $cmd)
    {
        $this->traceId= (string) Uuid::uuid4();
        $this->cachedContext['cmd_name'] = $cmd->getName();
        $this->cachedContext['akeneo_context'] = $this->boundedContextResolver->fromCommand($cmd) ?: "Unknown context";
    }

    public function insertContext(string $key, string $value): void
    {
        $this->cachedContext[$key] = $value;
    }
}
