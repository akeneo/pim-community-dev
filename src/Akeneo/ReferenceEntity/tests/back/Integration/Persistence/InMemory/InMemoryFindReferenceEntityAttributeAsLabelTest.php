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

namespace Akeneo\ReferenceEntity\Integration\Persistence\InMemory;

use Akeneo\ReferenceEntity\Common\Fake\InMemoryFindReferenceEntityAttributeAsLabel;
use Akeneo\ReferenceEntity\Common\Fake\InMemoryReferenceEntityRepository;
use Akeneo\ReferenceEntity\Domain\Model\Image;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\AttributeAsImageReference;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\AttributeAsLabelReference;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntity;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;

class InMemoryFindReferenceEntityAttributeAsLabelTest extends TestCase
{
    /** @var InMemoryFindReferenceEntityAttributeAsLabel */
    private $findReferenceEntityAttributeAsLabel;

    /** @var InMemoryReferenceEntityRepository */
    private $referenceEntityRepository;

    public function setUp(): void
    {
        $this->referenceEntityRepository = new InMemoryReferenceEntityRepository(
            new EventDispatcher()
        );
        $this->findReferenceEntityAttributeAsLabel = new InMemoryFindReferenceEntityAttributeAsLabel($this->referenceEntityRepository);
    }

    /**
     * @test
     */
    public function it_finds_the_attribute_as_label_of_a_reference_entity()
    {
        $expectedAttributeAsLabel = AttributeAsLabelReference::createFromNormalized('label_designer_fingerprint');
        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString('designer');

        $referenceEntity = ReferenceEntity::createWithAttributes(
            $referenceEntityIdentifier,
            [],
            Image::createEmpty(),
            $expectedAttributeAsLabel,
            AttributeAsImageReference::noReference()
        );
        $this->referenceEntityRepository->create($referenceEntity);

        $attributeAsLabel = ($this->findReferenceEntityAttributeAsLabel)($referenceEntityIdentifier);

        $this->assertSame($expectedAttributeAsLabel, $attributeAsLabel);
    }

    /**
     * @test
     */
    public function it_returns_an_empty_attribute_as_label_if_the_reference_entity_was_not_found()
    {
        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString('unknown');
        $attributeAsLabel = ($this->findReferenceEntityAttributeAsLabel)($referenceEntityIdentifier);

        $this->assertTrue($attributeAsLabel->isEmpty());
    }
}
