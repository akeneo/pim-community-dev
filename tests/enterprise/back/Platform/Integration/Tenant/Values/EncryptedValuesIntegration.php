<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2023 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AkeneoTestEnterprise\Platform\Integration\Tenant\Values;

use Akeneo\Platform\Component\Tenant\Domain\ContextValueDecrypter;
use Akeneo\Platform\Component\Tenant\Domain\Values\EncryptedValues;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Webmozart\Assert\InvalidArgumentException;

final class EncryptedValuesIntegration extends TestCase
{
    /** @test */
    public function it_is_created_from_encrypted_payload(): void
    {
        $payload = [
            'data' => 'my_data',
            'iv' => 'my_iv',
        ];

        $encryptedValues = EncryptedValues::create($payload);

        Assert::assertSame(
            $payload,
            $encryptedValues->normalize()
        );
    }

    /** @test */
    public function it_throws_if_missing_data(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Encrypted payload must have a "data" key'
        );

        $payload = [
            'not_data' => 'foobar',
            'iv' => 'my_iv',
        ];

        EncryptedValues::create($payload);
    }

    /** @test */
    public function it_throws_if_missing_iv(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Encrypted payload must have an "iv" key'
        );

        $payload = [
            'data' => 'foobar',
            'not_iv' => 'my_iv',
        ];

        EncryptedValues::create($payload);
    }

    /** @test */
    public function it_decodes_data(): void
    {
        $decoder = new ContextValueDecrypter('NDyClnH/qM6JfUR7c8Yc0kKBhaqP554EpHha4HTHQ/Y=');

        $payload = [
            "data" => "qbbkq1rrnYyj1UkcJ6TR/qTA/ZEd7kPR7Ajyq2vgxUg=",
            "iv" => "90d68e58aa2918f137ea2de4c07463ac",
        ];

        $value = EncryptedValues::create($payload);

        Assert::assertSame(
            ['foo' => 'bar', 'bar' => 'baz'],
            $value->decode($decoder)
        );
    }
}
