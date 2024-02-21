<?php

declare(strict_types=1);

namespace Akeneo\Platform\Bundle\UIBundle\Provider\ContentSecurityPolicy;

interface ContentSecurityPolicyProviderInterface
{
    public function getContentSecurityPolicy(): array;
}
