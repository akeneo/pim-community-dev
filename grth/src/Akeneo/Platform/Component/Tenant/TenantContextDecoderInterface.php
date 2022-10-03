<?php

namespace Akeneo\Platform\Component\Tenant;

interface TenantContextDecoderInterface
{
    public function decode(string $encodedValues): array;
}
