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

namespace AkeneoTestEnterprise\Platform\Integration\Tenant;

use Akeneo\Platform\Component\Tenant\Domain\ContextValueDecrypter;
use Akeneo\Platform\Component\Tenant\Domain\Exception\TenantContextDecoderException;
use Akeneo\Platform\Component\Tenant\Domain\Exception\TenantContextInvalidFormatException;
use Akeneo\Platform\Component\Tenant\Domain\Exception\TenantContextNotFoundException;
use Akeneo\Platform\Component\Tenant\Domain\Exception\TenantContextNotReadyException;
use Akeneo\Platform\Component\Tenant\Domain\TenantContextFetcher;
use Akeneo\Platform\Component\Tenant\Infrastructure\FirestoreContextStore;
use Google\Cloud\Firestore\CollectionReference;
use Google\Cloud\Firestore\DocumentReference;
use Google\Cloud\Firestore\DocumentSnapshot;
use Google\Cloud\Firestore\FirestoreClient;
use Google\Cloud\Firestore\ValueMapper;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

final class TenantContextFetcherIntegration extends TestCase
{
    private const CACHE_TTL = 5;

    private TenantContextFetcher $tenantContextFetcher;
    private InMemoryContextStore $contextStore;

    /** @test */
    public function it_gets_the_tenant_context(): void
    {
        Assert::assertEquals(
            ['foo' => 'bar', 'bar' => 'baz'],
            $this->tenantContextFetcher->getTenantContext('some_tenant_id', $this->contextStore)
        );
        Assert::assertEquals(
            ['ONE' => 'two', 'TWO' => 'three'],
            $this->tenantContextFetcher->getTenantContext('some_other_tenant_id', $this->contextStore)
        );
        // new format
        Assert::assertEquals(
            ['foo' => 'bar', 'baz' => 'snafu', 'ONE' => 'two', 'TWO' => 'three'],
            $this->tenantContextFetcher->getTenantContext('tenant_format_v2', $this->contextStore)
        );
    }

    /** @test */
    public function it_gets_secret_value_overrided_by_plain_value(): void
    {
        Assert::assertEquals(
            [
                'ONE' => 'overrided',
                'TWO' => 'three',
                'baz' => 'snafu',
            ],
            $this->tenantContextFetcher->getTenantContext('value_override', $this->contextStore)
        );
    }

    /** @test */
    public function it_caches_the_context_for_subsequent_requests(): void
    {
        $initialContext = $this->tenantContextFetcher->getTenantContext(
            'some_tenant_id',
            $this->contextStore
        );
        $initialContextNewFormat = $this->tenantContextFetcher->getTenantContext(
            'tenant_format_v2',
            $this->contextStore
        );

        // clear values = '{"updated_context": "updated_value"}'
        $this->contextStore->addDocument(
            'some_tenant_id',
            [
                'status' => 'created',
                'context' => '{"data":"KLzn+p4OFTwWvyvYNscpvMdURRnVlzfDvTfxluNYv1CBDRSmfcRFqMYnRr+j1VnV","iv":"dfd0c5655a81118a268ddeb40660445d"}',
            ]
        );
        $this->contextStore->addDocument(
            'tenant_format_v2',
            [
                'status' => 'created',
                'context' => [
                    'plain_values' => [
                        "foobar" => "bazbaz",
                    ],
                    'secret_values' => [
                        "data" => "KLzn+p4OFTwWvyvYNscpvMdURRnVlzfDvTfxluNYv1CBDRSmfcRFqMYnRr+j1VnV",
                        "iv" => "dfd0c5655a81118a268ddeb40660445d",
                    ],
                ],
            ]
        );

        // get context from cache
        Assert::assertEquals(
            $initialContext,
            $this->tenantContextFetcher->getTenantContext('some_tenant_id', $this->contextStore)
        );
        Assert::assertEquals(
            $initialContextNewFormat,
            $this->tenantContextFetcher->getTenantContext('tenant_format_v2', $this->contextStore)
        );

        // wait until the cache ttl is expired
        sleep(self::CACHE_TTL + 1);

        Assert::assertEquals(
            ['updated_context' => 'updated_value'],
            $this->tenantContextFetcher->getTenantContext('some_tenant_id', $this->contextStore)
        );
        Assert::assertEquals(
            ['foobar' => 'bazbaz', 'updated_context' => 'updated_value'],
            $this->tenantContextFetcher->getTenantContext('tenant_format_v2', $this->contextStore)
        );
    }

    /** @test */
    public function it_throws_an_exception_if_the_tenant_is_unknown(): void
    {
        $this->expectException(TenantContextNotFoundException::class);
        $this->expectExceptionMessage('Unable to fetch context for the "unknown_tenant_id" tenant ID');

        $this->tenantContextFetcher->getTenantContext('unknown_tenant_id', $this->contextStore);
    }

    /** @test */
    public function it_retries_to_fetch_context_with_firestore(): void
    {
        $this->expectException(TenantContextNotFoundException::class);
        $this->expectExceptionMessage('Unable to fetch context for the "unknown_tenant_id" tenant ID');

        $maxRetry = 5;

        $document = $this->createMock(DocumentReference::class);
        $document->expects($this->exactly($maxRetry))
            ->method('snapshot')
            ->willReturn(
                new DocumentSnapshot(
                    reference: $document,
                    valueMapper: $this->createMock(ValueMapper::class),
                    info: [],
                    data: [],
                    exists: false,
                )
            );

        $collection = $this->createMock(CollectionReference::class);
        $collection->expects($this->exactly($maxRetry))
            ->method('document')
            ->willReturn($document);

        $firestoreClient = $this->createMock(FirestoreClient::class);
        $firestoreClient->expects($this->exactly($maxRetry))
            ->method('collection')
            ->willReturn($collection);

        $contextStore = new FirestoreContextStore($firestoreClient, 'my_collection');

        $this->tenantContextFetcher->getTenantContext('unknown_tenant_id', $contextStore);
    }

    /** @test */
    public function it_finds_context_with_firestore_after_retry(): void
    {
        $maxRetry = 3;

        $document = $this->createMock(DocumentReference::class);

        $existingSnapShot = new DocumentSnapshot(
            reference: $document,
            valueMapper: $this->createMock(ValueMapper::class),
            info: [],
            data: [
                'status' => 'created',
                'context' => [
                    'plain_values' => ["foo" => "bar", "baz" => "snafu"],
                    'secret_values' => [
                        "data" => "q7Hc+qhe2XPZOp7tl+BNru5+ZVENLwxVg7/jDYCy6LY=",
                        "iv" => "5724001ec68519d4fd2e8c1a76f1af2c",
                    ],
                ],
            ],
            exists: true,
        );
        $nonExistingSnapShot = new DocumentSnapshot(
            reference: $document,
            valueMapper: $this->createMock(ValueMapper::class),
            info: [],
            data: [],
            exists: false,
        );

        $document->expects($this->exactly($maxRetry))
            ->method('snapshot')
            ->will(
                $this->onConsecutiveCalls(
                    $nonExistingSnapShot,
                    $nonExistingSnapShot,
                    $existingSnapShot
                )
            );

        $collection = $this->createMock(CollectionReference::class);
        $collection->expects($this->exactly($maxRetry))
            ->method('document')
            ->willReturn($document);

        $firestoreClient = $this->createMock(FirestoreClient::class);
        $firestoreClient->expects($this->exactly($maxRetry))
            ->method('collection')
            ->willReturn($collection);

        $contextStore = new FirestoreContextStore($firestoreClient, 'my_collection');

        Assert::assertIsArray($this->tenantContextFetcher->getTenantContext('some_tenant_id', $contextStore));
    }

    /** @test */
    public function it_throws_an_exception_if_the_context_key_is_not_defined(): void
    {
        $this->expectException(TenantContextInvalidFormatException::class);
        $this->expectExceptionMessage(
            'Unable to fetch context for the "tenant_id_without_context" tenant ID: missing key in the document.'
        );

        $this->tenantContextFetcher->getTenantContext('tenant_id_without_context', $this->contextStore);
    }

    /** @test */
    public function it_throws_an_exception_if_the_v1_values_are_not_valid_json(): void
    {
        $this->expectException(TenantContextDecoderException::class);

        $this->tenantContextFetcher->getTenantContext('format_v1_with_invalid_values', $this->contextStore);
    }

    /** @test */
    public function it_throws_an_exception_if_the_v2_values_are_not_valid_json(): void
    {
        $this->expectException(TenantContextDecoderException::class);

        $this->tenantContextFetcher->getTenantContext('format_v2_with_invalid_values', $this->contextStore);
    }

    /** @test */
    public function it_throws_an_exception_if_the_tenant_is_not_ready(): void
    {
        $this->expectException(TenantContextNotReadyException::class);

        $this->tenantContextFetcher->getTenantContext('tenant_id_not_ready', $this->contextStore);
    }

    /** @test */
    public function it_throws_an_exception_if_the_tenant_is_deleted(): void
    {
        $this->expectException(TenantContextNotReadyException::class);

        $this->tenantContextFetcher->getTenantContext('tenant_id_deleted', $this->contextStore);
    }

    /**
     * Crypted values generated with grth/tests/back/Platform/resources/aes_encoder.js
     */
    protected function setUp(): void
    {
        $encryptionKey = 'NDyClnH/qM6JfUR7c8Yc0kKBhaqP554EpHha4HTHQ/Y=';

        $this->tenantContextFetcher = new TenantContextFetcher(
            logger: new NullLogger(),
            tenantContextDecoder: new ContextValueDecrypter($encryptionKey),
            cacheTtl: self::CACHE_TTL,
        );

        $this->contextStore = new InMemoryContextStore();

        /*
         * clear context = {
         *    'foo' => 'bar',
         *    'bar' => 'baz',
         * ]
         */
        $this->contextStore->addDocument(
            'some_tenant_id',
            [
                'status' => 'created',
                'context' => '{"data":"qbbkq1rrnYyj1UkcJ6TR/qTA/ZEd7kPR7Ajyq2vgxUg=","iv":"90d68e58aa2918f137ea2de4c07463ac"}',
            ]
        );

        /*
         * clear context = {
         *    'ONE' => 'two',
         *    'TWO' => 'three',
         * ]
         */
        $this->contextStore->addDocument(
            'some_other_tenant_id',
            [
                'status' => 'created',
                'context' => '{"data":"q7Hc+qhe2XPZOp7tl+BNru5+ZVENLwxVg7/jDYCy6LY=","iv":"5724001ec68519d4fd2e8c1a76f1af2c"}',
            ]
        );

        /*
         * clear secrets values = {
         *    'ONE' => 'two',
         *    'TWO' => 'three',
         * ]
         */
        $this->contextStore->addDocument(
            'tenant_format_v2',
            [
                'status' => 'created',
                'context' => [
                    'plain_values' => ["foo" => "bar", "baz" => "snafu"],
                    'secret_values' => [
                        "data" => "q7Hc+qhe2XPZOp7tl+BNru5+ZVENLwxVg7/jDYCy6LY=",
                        "iv" => "5724001ec68519d4fd2e8c1a76f1af2c",
                    ],
                ],
            ]
        );

        /*
         * clear secrets values = {
         *    'ONE' => 'two',
         *    'TWO' => 'three',
         * ]
         */
        $this->contextStore->addDocument(
            'value_override',
            [
                'status' => 'created',
                'context' => [
                    'plain_values' => ['ONE' => 'overrided', 'baz' => 'snafu'],
                    'secret_values' => [
                        "data" => "q7Hc+qhe2XPZOp7tl+BNru5+ZVENLwxVg7/jDYCy6LY=",
                        "iv" => "5724001ec68519d4fd2e8c1a76f1af2c",
                    ],
                ],
            ]
        );

        // clear context = '{"foo": "bar",, "baz": "snafu"}'
        $this->contextStore->addDocument(
            'format_v1_with_invalid_values',
            [
                'status' => 'created',
                'context' => '{"data":"VgvimYiLnlxArlu3RTMbG41dDANh9xne6d71p/AeCQI=","iv":"5f90664b2fca29dd597d1e741a6f8255"}',
            ]
        );

        // clear secret_values = '{"foo": "bar",, "baz": "snafu"}'
        $this->contextStore->addDocument(
            'format_v2_with_invalid_values',
            [
                'status' => 'created',
                'context' => [
                    'plain_values' => ["foo" => "bar", "baz" => "snafu"],
                    'secret_values' => [
                        "data" => "VgvimYiLnlxArlu3RTMbG41dDANh9xne6d71p/AeCQI=",
                        "iv" => "5f90664b2fca29dd597d1e741a6f8255",
                    ],
                ],
            ]
        );

        $this->contextStore->addDocument(
            'tenant_id_without_context',
            [
                'status' => 'created',
            ]
        );

        $this->contextStore->addDocument(
            'tenant_id_without_status',
            [
                'context' => '{"data":"VgvimYiLnlxArlu3RTMbG41dDANh9xne6d71p/AeCQI=","iv":"5f90664b2fca29dd597d1e741a6f8255"}',
            ]
        );

        $this->contextStore->addDocument(
            'tenant_id_not_ready',
            [
                'status' => 'creation_pending',
                'context' => '{"data":"VgvimYiLnlxArlu3RTMbG41dDANh9xne6d71p/AeCQI=","iv":"5f90664b2fca29dd597d1e741a6f8255"}',
            ]
        );

        $this->contextStore->addDocument(
            'tenant_id_deleted',
            [
                'status' => 'deleted',
                'context' => '{"data":"VgvimYiLnlxArlu3RTMbG41dDANh9xne6d71p/AeCQI=","iv":"5f90664b2fca29dd597d1e741a6f8255"}',
            ]
        );
    }

    protected function tearDown(): void
    {
        \apcu_clear_cache();
    }
}
