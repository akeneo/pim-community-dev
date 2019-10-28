<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Controller\ExternalApi;

use Akeneo\Tool\Bundle\ApiBundle\Cache\WarmupQueryCache;
use Symfony\Component\HttpFoundation\Request;

/**
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class DummyWarmupQueryCache implements WarmupQueryCache
{
    /**
     * {@inheritdoc}
     */
    public function fromRequest(Request $request): void
    {
    }
}
