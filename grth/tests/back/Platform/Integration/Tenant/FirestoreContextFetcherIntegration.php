<?php

declare(strict_types=1);

namespace AkeneoTestEnterprise\Platform\Integration\Tenant;

use Akeneo\Platform\Component\Tenant\Exception\TenantContextInvalidFormatException;
use Akeneo\Platform\Component\Tenant\Exception\TenantContextNotFoundException;
use Akeneo\Platform\Component\Tenant\Exception\TenantContextNotReadyException;
use Akeneo\Platform\Component\Tenant\FirestoreContextFetcher;
use Akeneo\Platform\Component\Tenant\TenantContextDecoder;
use Akeneo\Platform\Component\Tenant\TenantContextDecoderException;
use Google\Cloud\Firestore\CollectionReference;
use Google\Cloud\Firestore\FirestoreClient;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

final class FirestoreContextFetcherIntegration extends TestCase
{
    private const TEST_COLLECTION = 'test_tenant_contexts';

    private FirestoreContextFetcher $firestoreContextFetcher;

    /** @test */
    public function it_gets_the_tenant_context(): void
    {
        Assert::assertSame(
            ['foo' => 'bar', 'bar' => 'baz'],
            $this->firestoreContextFetcher->getTenantContext('some_tenant_id')
        );
        Assert::assertSame(
            ['ONE' => 'two', 'TWO' => 'three'],
            $this->firestoreContextFetcher->getTenantContext('some_other_tenant_id')
        );
    }

    /** @test */
    public function it_caches_the_context_for_subsequent_requests(): void
    {
        $initialContext = $this->firestoreContextFetcher->getTenantContext('some_tenant_id');

        // clear values = '{"updated_context": "updated_value"}'
        $this->firestoreCollection()->document('some_tenant_id')->set(
            [
                'status' => 'created',
                'context' => '{"data":"KLzn+p4OFTwWvyvYNscpvMdURRnVlzfDvTfxluNYv1CBDRSmfcRFqMYnRr+j1VnV","iv":"dfd0c5655a81118a268ddeb40660445d"}',
            ]
        );

        // get context from cache
        Assert::assertSame($initialContext, $this->firestoreContextFetcher->getTenantContext('some_tenant_id'));

        // wait until the cache ttl is expired
        sleep(6);

        Assert::assertSame(
            ['updated_context' => 'updated_value'],
            $this->firestoreContextFetcher->getTenantContext('some_tenant_id')
        );
    }

    /** @test */
    public function it_throws_an_exception_if_the_tenant_is_unknown(): void
    {
        $this->expectException(TenantContextNotFoundException::class);
        $this->expectExceptionMessage('Unable to fetch context for the "unknown_tenant_id" tenant ID');

        $this->firestoreContextFetcher->getTenantContext('unknown_tenant_id');
    }

    /** @test */
    public function it_throws_an_exception_if_the_context_key_is_not_defined(): void
    {
        $this->expectException(TenantContextInvalidFormatException::class);
        $this->expectExceptionMessage(
            'Unable to fetch context for the "tenant_id_without_context" tenant ID: missing key in the document.'
        );

        $this->firestoreContextFetcher->getTenantContext('tenant_id_without_context');
    }

    /** @test */
    public function it_throws_an_exception_if_the_values_are_not_valid_json(): void
    {
        $this->expectException(TenantContextDecoderException::class);

        $this->firestoreContextFetcher->getTenantContext('tenant_id_with_invalid_values');
    }

    /** @test */
    public function it_throws_an_exception_if_the_tenant_is_not_ready(): void
    {
        $this->expectException(TenantContextNotReadyException::class);

        $this->firestoreContextFetcher->getTenantContext('tenant_id_not_ready');
    }

    /**
     * Crypted values generated with grth/tests/back/Platform/resources/aes_encoder.js
     */
    protected function setUp(): void
    {
        $encryptionKey = 'NDyClnH/qM6JfUR7c8Yc0kKBhaqP554EpHha4HTHQ/Y=';
        $collection = $this->firestoreCollection();

        /*
         * clear values = {
         *    'foo' => 'bar',
         *    'bar' => 'baz',
         * ]
         */
        $collection->document('some_tenant_id')->create([
            'status' => 'created',
            'context' => '{"data":"qbbkq1rrnYyj1UkcJ6TR/qTA/ZEd7kPR7Ajyq2vgxUg=","iv":"90d68e58aa2918f137ea2de4c07463ac"}',
        ]);

        /*
         * clear values = {
         *    'ONE' => 'two',
         *    'TWO' => 'three',
         * ]
         */
        $collection->document('some_other_tenant_id')->create([
            'status' => 'created',
            'context' => '{"data":"q7Hc+qhe2XPZOp7tl+BNru5+ZVENLwxVg7/jDYCy6LY=","iv":"5724001ec68519d4fd2e8c1a76f1af2c"}',
        ]);

        // clear values = '{"foo": "bar",, "baz": "snafu"}'
        $collection->document('tenant_id_with_invalid_values')->create([
            'status' => 'created',
            'context' => '{"data":"VgvimYiLnlxArlu3RTMbG41dDANh9xne6d71p/AeCQI=","iv":"5f90664b2fca29dd597d1e741a6f8255"}',
        ]);

        $collection->document('tenant_id_without_context')->create([
            'status' => 'created',
        ]);

        $collection->document('tenant_id_without_status')->create([
            'context' => '{"data":"VgvimYiLnlxArlu3RTMbG41dDANh9xne6d71p/AeCQI=","iv":"5f90664b2fca29dd597d1e741a6f8255"}',
        ]);

        $collection->document('tenant_id_not_ready')->create([
            'status' => 'creation_pending',
            'context' => '{"data":"VgvimYiLnlxArlu3RTMbG41dDANh9xne6d71p/AeCQI=","iv":"5f90664b2fca29dd597d1e741a6f8255"}',
        ]);

        $this->firestoreContextFetcher = new FirestoreContextFetcher(
            logger: new NullLogger(),
            tenantContextDecoder: new TenantContextDecoder($encryptionKey),
            googleProjectId: $_ENV['GOOGLE_CLOUD_PROJECT'],
            collection: self::TEST_COLLECTION,
            cacheTtl: 5,
        );
    }

    protected function tearDown(): void
    {
        foreach ($this->firestoreCollection()->documents() as $document) {
            $document->reference()->delete();
        }
        \apcu_clear_cache();
    }

    private function firestoreCollection(): CollectionReference
    {
        return (new FirestoreClient(
            [
                'projectId' => $_ENV['GOOGLE_CLOUD_PROJECT'],
            ]
        ))->collection(self::TEST_COLLECTION);
    }
}
