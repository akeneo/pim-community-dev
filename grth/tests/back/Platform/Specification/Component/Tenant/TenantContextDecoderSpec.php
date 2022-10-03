<?php

namespace Specification\Akeneo\Platform\Component\Tenant;

use Akeneo\Platform\Component\Tenant\TenantContextDecoder;
use Akeneo\Platform\Component\Tenant\TenantContextDecoderInterface;
use PhpSpec\ObjectBehavior;

class TenantContextDecoderSpec extends ObjectBehavior
{
    function it_is_a_tenant_values_decoder()
    {
        $this->shouldImplement(TenantContextDecoderInterface::class);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(TenantContextDecoder::class);
    }

    function it_decodes_context_values()
    {
        $expectedValues = [
            'APP_VALUE1' => 'foo',
            'APP_VALUE2' => 'bar',
        ];

        $encodedValues = \json_encode($expectedValues);

        $this->decode($encodedValues)->shouldReturn($expectedValues);
    }
}
