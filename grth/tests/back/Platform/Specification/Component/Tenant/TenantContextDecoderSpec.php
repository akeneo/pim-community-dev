<?php

namespace Specification\Akeneo\Platform\Component\Tenant;

use Akeneo\Platform\Component\Tenant\TenantContextDecoder;
use Akeneo\Platform\Component\Tenant\TenantContextDecoderException;
use Akeneo\Platform\Component\Tenant\TenantContextDecoderInterface;
use PhpSpec\ObjectBehavior;

class TenantContextDecoderSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith('NDyClnH/qM6JfUR7c8Yc0kKBhaqP554EpHha4HTHQ/Y=');
    }

    function it_is_a_tenant_values_decoder()
    {
        $this->shouldImplement(TenantContextDecoderInterface::class);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(TenantContextDecoder::class);
    }

    /**
     * Crypted values generated with grth/tests/back/Platform/resources/aes_encoder.js
     */
    function it_decodes_encrypted_context_values()
    {
        $expectedValues = [
            'foo' => 'bar',
            'bar' => 'baz',
        ];

        $encryptedValues =
            '{"data":"qbbkq1rrnYyj1UkcJ6TR/qTA/ZEd7kPR7Ajyq2vgxUg=","iv":"90d68e58aa2918f137ea2de4c07463ac"}';

        $this->decode($encryptedValues)->shouldReturn($expectedValues);
    }

    function it_throws_on_invalid_hex_iv()
    {
        $encryptedValues =
            '{"data":"qbbkq1rrnYyj1UkcJ6TR/qTA/ZEd7kPR7Ajyq2vgxUg=","iv":"bad_iv"}';

        $this
            ->shouldThrow(new TenantContextDecoderException('Unable to decode tenant values payload.'))
            ->during('decode', [$encryptedValues]);
    }

    function it_throws_on_invalid_crypted_data()
    {
        $encryptedValues =
            '{"data":"invalid","iv":"90d68e58aa2918f137ea2de4c07463ac"}';

        $this
            ->shouldThrow(new TenantContextDecoderException('Unable to decrypt tenant values.'))
            ->during('decode', [$encryptedValues]);
    }

    function it_throws_on_invalid_json()
    {
        // clear values = '{"foo": "bar",, "baz": "snafu"}'
        $encryptedValues =
            '{"data":"VgvimYiLnlxArlu3RTMbG41dDANh9xne6d71p/AeCQI=","iv":"5f90664b2fca29dd597d1e741a6f8255"}';

        $this
            ->shouldThrow(new TenantContextDecoderException('Decrypted values is not a valid json string.'))
            ->during('decode', [$encryptedValues]);
    }

    function it_throws_on_invalid_json_map()
    {
        // clear values = '{"foo": "bar", "baz": ["in_array"]}'
        $encryptedValues =
            '{"data":"8hmjdChBhzysoMDA8a04WsngnpV9EG5CZarPtLOnvY4l4Z9ICRWEoRnLmXnH229x","iv":"25e62a45f89f02f53e5ede9c860f8c16"}';

        $this
            ->shouldThrow(new TenantContextDecoderException('Tenant values is not a valid json map.'))
            ->during('decode', [$encryptedValues]);
    }
}
