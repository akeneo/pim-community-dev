<?php
declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Integration\PreviewGenerator;

use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeCode;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIsRequired;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeOrder;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\Url\MediaType;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\Url\Prefix;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\Url\Suffix;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\UrlAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Image;
use Akeneo\ReferenceEntity\Domain\Model\LabelCollection;
use Akeneo\ReferenceEntity\Domain\Model\Record\Record;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\ChannelReference;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\LocaleReference;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\UrlData;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\Value;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\ValueCollection;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntity;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Infrastructure\PreviewGenerator\PreviewGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class ImageGeneratorTest extends KernelTestCase
{
    /** @var KernelInterface|null */
    protected $testKernel;

    /** @var PreviewGeneratorInterface */
    private $imageGenerator;

    /** @var ReferenceEntityIdentifier */
    private $referenceEntityIdentifier;

    /** @var AttributeIdentifier */
    private $attributeIdentifier;

    public function setUp(): void
    {
        if (null === $this->testKernel) {
            $this->bootTestKernel();
        }
        $this->resetDB();
        $this->imageGenerator = $this->get('akeneo_referenceentity.application.generator.image_generator');
    }

    protected function bootTestKernel(): void
    {
        $this->testKernel = new \AppKernelTest('test', false);
        $this->testKernel->boot();
    }

    /**
     * @test
     */
    public function it_get_the_thumbnail_of_image_url_attribute()
    {
        $this->loadReferenceEntity();
        $this->loadUrlAttribute();
        $this->loadRecord();

        $attributeRepository = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.attribute');
        $attribute = $attributeRepository->getByIdentifier($this->attributeIdentifier);
        $this->imageGenerator->generate('google-logo', $attribute, 'dam_thumbnail');
    }

    private function loadReferenceEntity(): void
    {
        $referenceEntityRepository = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.reference_entity');
        $this->referenceEntityIdentifier = ReferenceEntityIdentifier::fromString('designer');
        $referenceEntity = ReferenceEntity::create(
            $this->referenceEntityIdentifier,
            [
                'fr_FR' => 'Concepteur',
                'en_US' => 'Designer',
            ],
            Image::createEmpty()
        );
        $referenceEntityRepository->create($referenceEntity);
    }

    private function loadUrlAttribute()
    {
        $attributeRepository = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.attribute');
        $this->attributeIdentifier = AttributeIdentifier::fromString('dam_image_designer_fingerprint');
        $attribute = UrlAttribute::create(
            $this->attributeIdentifier,
            $this->referenceEntityIdentifier,
            AttributeCode::fromString('color'),
            LabelCollection::fromArray([]),
            AttributeOrder::fromInteger(2),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false),
            Prefix::fromString('https://ressources.blogdumoderateur.com/2013/10/'),
            Suffix::empty(),
            MediaType::fromString(MediaType::IMAGE)
        );
        $attributeRepository->create($attribute);
    }

    private function loadRecord()
    {
        $recordRepository = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.record');
        $value = Value::create(
            $this->attributeIdentifier,
            ChannelReference::noReference(),
            LocaleReference::noReference(),
            UrlData::fromString('12345')
        );
        $record = Record::create(
            RecordIdentifier::fromString('starck_designer_fingerprint'),
            $this->referenceEntityIdentifier,
            RecordCode::fromString('starck'),
            ValueCollection::fromValues([$value])
        );

        $recordRepository->create($record);
    }

    /*
     * @return mixed
     */
    protected function get(string $service)
    {
        return $this->testKernel->getContainer()->get($service);
    }

    private function resetDB(): void
    {
        $this->get('akeneoreference_entity.tests.helper.database_helper')->resetDatabase();
    }
}
