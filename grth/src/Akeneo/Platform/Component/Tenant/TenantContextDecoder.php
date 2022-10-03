<?php

namespace Akeneo\Platform\Component\Tenant;

use Webmozart\Assert\Assert;

final class TenantContextDecoder implements TenantContextDecoderInterface
{
    public function decode(string $encodedValues): array
    {
        $decoded = \json_decode(json: $encodedValues, associative: true, flags: \JSON_THROW_ON_ERROR);
        Assert::isMap($decoded);

        return $decoded;
    }
}
