<?php

declare(strict_types=1);

namespace AkeneoTestEnterprise\Pim\Permission\Integration\Persistence\ORM\Locale;

use Akeneo\Channel\Component\Model\LocaleInterface;
use Akeneo\Pim\Permission\Bundle\Persistence\ORM\Locale\GetLocaleReferenceFromCode;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;

class GetLocaleReferenceFromCodeIntegration extends TestCase
{
    private GetLocaleReferenceFromCode $query;

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

        $this->query = $this->get(GetLocaleReferenceFromCode::class);
    }

    public function testItReturnsTheLocaleEntity(): void
    {
        $entity = $this->query->execute('fr_FR');

        $this->assertNotNull($entity);
        $this->assertInstanceOf(LocaleInterface::class, $entity);
    }

    public function testItReturnsNullWhenTheLocaleCodeIsUnknown(): void
    {
        $entity = $this->query->execute('unknown group code');

        $this->assertNull($entity);
    }
}
