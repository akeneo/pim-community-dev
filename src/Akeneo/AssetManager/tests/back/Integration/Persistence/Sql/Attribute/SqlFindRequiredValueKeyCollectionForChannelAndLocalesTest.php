<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\ReferenceEntity\Integration\Persistence\Sql\Attribute;

use Akeneo\ReferenceEntity\Domain\Model\ChannelIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\LocaleIdentifierCollection;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\FindRequiredValueKeyCollectionForChannelAndLocalesInterface;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\ValueKeyCollection;
use Akeneo\ReferenceEntity\Domain\Repository\AttributeRepositoryInterface;
use Akeneo\ReferenceEntity\Integration\SqlIntegrationTestCase;

/**
 * ----------------------------------
 * |  Channel  |  Activated locales |
 * |-----------|--------------------|
 * | ecommerce | fr_FR, en_US       |
 * | mobile    | de_DE              |
 * ----------------------------------
 */
class SqlFindRequiredValueKeyCollectionForChannelAndLocalesTest extends SqlIntegrationTestCase
{
    /** @var FindRequiredValueKeyCollectionForChannelAndLocalesInterface */
    private $findRequiredValueKeyCollection;

    /** @var AttributeRepositoryInterface */
    private $attributeRepository;

    /** @var array */
    private $fixturesDesigner;

    /** @var array */
    private $fixturesCountry;

    public function setUp(): void
    {
        parent::setUp();

        $this->findRequiredValueKeyCollection = $this->get('akeneo_referenceentity.infrastructure.persistence.query.find_required_value_key_collection_for_channel_and_locales');
        $this->attributeRepository = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.attribute');
        $this->resetDB();
        $this->loadFixtures();
    }

    /**
     * @test
     */
    public function it_returns_the_value_key_collection_of_required_attributes_of_a_reference_entity()
    {
        $designer = ReferenceEntityIdentifier::fromString('designer');
        $channel = ChannelIdentifier::fromCode('ecommerce');
        $locales = LocaleIdentifierCollection::fromNormalized(['fr_FR', 'en_US', 'en_AU']);

        $actualValueKeyCollection = $this->findRequiredValueKeyCollection->find($designer, $channel, $locales);

        $this->assertInstanceOf(ValueKeyCollection::class, $actualValueKeyCollection);
        $normalizedActualValueKeyCollection = $actualValueKeyCollection->normalize();
        $this->assertCount(6, $normalizedActualValueKeyCollection);

        $this->assertContains(sprintf('%s', $this->fixturesDesigner['attributes']['country']->getIdentifier()), $normalizedActualValueKeyCollection);
        $this->assertContains(sprintf('%s_ecommerce', $this->fixturesDesigner['attributes']['main_image']->getIdentifier()), $normalizedActualValueKeyCollection);
        $this->assertContains(sprintf('%s_ecommerce_fr_FR', $this->fixturesDesigner['attributes']['long_description']->getIdentifier()), $normalizedActualValueKeyCollection);
        $this->assertContains(sprintf('%s_ecommerce_en_US', $this->fixturesDesigner['attributes']['long_description']->getIdentifier()), $normalizedActualValueKeyCollection);
        $this->assertContains(sprintf('%s_fr_FR', $this->fixturesDesigner['attributes']['materials']->getIdentifier()), $normalizedActualValueKeyCollection);
        $this->assertContains(sprintf('%s_en_US', $this->fixturesDesigner['attributes']['materials']->getIdentifier()), $normalizedActualValueKeyCollection);

        $this->assertNotContains(sprintf('%s', $this->fixturesDesigner['attributes']['nickname']->getIdentifier()), $normalizedActualValueKeyCollection);
    }

    /**
     * @test
     */
    public function it_does_not_return_value_keys_for_the_locales_that_are_not_activated_for_the_channel()
    {
        $this->resetDB();
        $this->fixturesDesigner = $this->fixturesLoader
            ->referenceEntity('designer')
            ->withAttributes(['long_description'])
            ->load();

        $designer = ReferenceEntityIdentifier::fromString('designer');
        $channel = ChannelIdentifier::fromCode('mobile');
        $locales = LocaleIdentifierCollection::fromNormalized(['fr_FR', 'en_US']);

        $valueKeyCollection = $this->findRequiredValueKeyCollection->find($designer, $channel, $locales);
        $this->assertInstanceOf(ValueKeyCollection::class, $valueKeyCollection);
        $this->assertEmpty($valueKeyCollection->normalize());
    }

    private function resetDB(): void
    {
        $this->get('akeneoreference_entity.tests.helper.database_helper')->resetDatabase();
    }

    private function loadFixtures(): void
    {
        $this->fixturesDesigner = $this->fixturesLoader
            ->referenceEntity('designer')
            ->withAttributes(['country', 'main_image', 'long_description', 'materials', 'nickname'])
            ->load();

        $this->fixturesCountry = $this->fixturesLoader
            ->referenceEntity('country')
            ->load();
    }
}
