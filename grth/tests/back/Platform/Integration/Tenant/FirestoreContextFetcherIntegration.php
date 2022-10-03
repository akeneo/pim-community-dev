<?php

declare(strict_types=1);

namespace AkeneoTestEnterprise\Platform\Integration\Tenant;

use Akeneo\Platform\Component\Tenant\FirestoreContextFetcher;
use Akeneo\Platform\Component\Tenant\TenantContextDecoder;
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
            ['FOO' => 'bar', 'BAR' => 'baz'],
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
        $this->firestoreCollection()->document('some_tenant_id')->set(
            ['values' => \json_encode(['updated_context' => 'updated_value'])]
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
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Unable to fetch context for the "unknown_tenant_id" tenant ID');

        $this->firestoreContextFetcher->getTenantContext('unknown_tenant_id');
    }

    /** @test */
    public function it_throws_an_exception_if_the_values_key_is_not_defined(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Unable to fetch context for the "tenant_id_without_values" tenant ID');

        $this->firestoreContextFetcher->getTenantContext('tenant_id_without_values');
    }

    /** @test */
    public function it_throws_an_exception_if_the_values_are_not_valid_json(): void
    {
        $this->expectException(\JsonException::class);

        $this->firestoreContextFetcher->getTenantContext('tenant_id_with_invalid_values');
    }

    protected function setUp(): void
    {
        $collection = $this->firestoreCollection();
        $collection->document('some_tenant_id')->create([
            'values' => \json_encode(
                [
                    'FOO' => 'bar',
                    'BAR' => 'baz',
                ]
            ),
        ]);
        $collection->document('some_other_tenant_id')->create([
            'values' => \json_encode(
                [
                    'ONE' => 'two',
                    'TWO' => 'three',
                ]
            ),
        ]);
        $collection->document('tenant_id_without_values')->create([
            'foo' => 'bar',
        ]);
        $collection->document('tenant_id_with_invalid_values')->create([
            'values' => '{"invalid_json"'
        ]);

        $this->firestoreContextFetcher = new FirestoreContextFetcher(
            logger: new NullLogger(),
            tenantContextDecoder: new TenantContextDecoder(),
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
                'projectId' => $_ENV['GOOGLE_CLOUD_PROJECT']
            ]
        ))->collection(self::TEST_COLLECTION);
    }
}
