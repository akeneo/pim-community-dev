<?php

declare(strict_types=1);

namespace AkeneoTestEnterprise\Pim\Permission\Integration\Persistence\ORM\Locale;

use Akeneo\Channel\Infrastructure\Component\Model\LocaleInterface;
use Akeneo\Pim\Permission\Bundle\Persistence\ORM\Locale\GetActiveLocaleReferenceFromCode;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;

class GetActiveLocaleReferenceFromCodeIntegration extends TestCase
{
    private GetActiveLocaleReferenceFromCode $query;

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->query = $this->get(GetActiveLocaleReferenceFromCode::class);
    }

    public function testItReturnsTheLocaleEntity(): void
    {
        $entity = $this->query->execute('en_US');

        $this->assertNotNull($entity);
        $this->assertInstanceOf(LocaleInterface::class, $entity);
    }

    public function testItReturnsNullWhenTheLocaleIsInactive(): void
    {
        $entity = $this->query->execute('fr_FR');

        $this->assertNull($entity);
    }

    public function testItReturnsNullWhenTheLocaleCodeIsUnknown(): void
    {
        $entity = $this->query->execute('unknown locale code');

        $this->assertNull($entity);
    }
}
