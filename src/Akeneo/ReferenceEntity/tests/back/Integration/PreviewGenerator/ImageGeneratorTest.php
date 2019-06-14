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
use Akeneo\ReferenceEntity\Infrastructure\PreviewGenerator\ImageGenerator;
use Akeneo\ReferenceEntity\Infrastructure\PreviewGenerator\PreviewGeneratorInterface;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class ImageGeneratorTest extends KernelTestCase
{
    private const Url = 'https://akeneodemo.getbynder.com/m/1e567bef001b08fa/';
    private const filename = 'Akeneo-DSC_2109-2.jpg';

    /** @var KernelInterface|null */
    protected $testKernel;

    /** @var PreviewGeneratorInterface */
    private $imageGenerator;

    /** @var ReferenceEntityIdentifier */
    private $referenceEntityIdentifier;

    /** @var AttributeIdentifier */
    private $attributeIdentifier;

    /** @var CacheManager */
    private $cacheManager;

    public function setUp(): void
    {
        if (null === $this->testKernel) {
            $this->bootTestKernel();
        }
        $this->resetDB();
        $this->imageGenerator = $this->get('akeneo_referenceentity.application.generator.image_generator');
        $this->cacheManager = $this->get('liip_imagine.cache.manager');
        $this->cacheManager->remove();
    }

    protected function bootTestKernel(): void
    {
        $this->testKernel = new \AppKernelTest('test', false);
        $this->testKernel->boot();
    }

    /**
     * @test
     */
    public function it_can_support_only_media_type_image_of_an_url_attribute()
    {
        $this->loadReferenceEntity();
        $this->loadUrlAttribute(MediaType::IMAGE);
        $this->loadRecord();

        $attributeRepository = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.attribute');
        $attribute = $attributeRepository->getByIdentifier($this->attributeIdentifier);
        $isSupported = $this->imageGenerator->supports(self::filename, $attribute, ImageGenerator::THUMBNAIL_TYPE);

        $this->assertTrue($isSupported);

        $this->resetDB();

        $this->loadReferenceEntity();
        $this->loadUrlAttribute(MediaType::OTHER);
        $this->loadRecord();

        $attributeRepository = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.attribute');
        $attribute = $attributeRepository->getByIdentifier($this->attributeIdentifier);
        $isSupported = $this->imageGenerator->supports(self::filename, $attribute, ImageGenerator::THUMBNAIL_TYPE);

        $this->assertFalse($isSupported);
    }

    /**
     * @test
     */
    public function it_can_support_only_supported_type_image_of_an_url_attribute()
    {
        $this->loadReferenceEntity();
        $this->loadUrlAttribute(MediaType::IMAGE);
        $this->loadRecord();

        $attributeRepository = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.attribute');
        $attribute = $attributeRepository->getByIdentifier($this->attributeIdentifier);
        $isSupported = $this->imageGenerator->supports(self::filename, $attribute, ImageGenerator::THUMBNAIL_TYPE);

        $this->assertTrue($isSupported);

        $isSupported = $this->imageGenerator->supports(self::filename, $attribute, 'preview');

        $this->assertFalse($isSupported);
    }

    /**
     * @test
     */
    public function it_get_a_preview_for_an_image_url_attribute()
    {
        $this->loadReferenceEntity();
        $this->loadUrlAttribute(MediaType::IMAGE);
        $this->loadRecord();

        $attributeRepository = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.attribute');
        $attribute = $attributeRepository->getByIdentifier($this->attributeIdentifier);
        $this->imageGenerator->supports('google-logo.png', $attribute, ImageGenerator::THUMBNAIL_TYPE);
        $previewImage = $this->imageGenerator->generate(self::filename, $attribute, ImageGenerator::THUMBNAIL_TYPE);
        
        $this->assertStringContainsString('media/cache/', $previewImage);
    }

    /**
     * @test
     */
    public function it_get_a_preview_for_an_image_url_attribute_from_the_cache()
    {
        $this->loadReferenceEntity();
        $this->loadUrlAttribute(MediaType::IMAGE);
        $this->loadRecord();

        $attributeRepository = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.attribute');
        $attribute = $attributeRepository->getByIdentifier($this->attributeIdentifier);
        $this->imageGenerator->supports('akeneo.png', $attribute, ImageGenerator::THUMBNAIL_TYPE);
        $previewImage = $this->imageGenerator->generate(self::filename, $attribute, ImageGenerator::THUMBNAIL_TYPE);

        $this->assertStringContainsString('media/cache/', $previewImage);

        $previewImage = $this->imageGenerator->generate(self::filename, $attribute, ImageGenerator::THUMBNAIL_TYPE);

        $this->assertStringContainsString('media/cache/', $previewImage);
    }

    /**
     * @test
     */
    public function it_get_a_default_preview_for_an_unknown_image_url()
    {
        $this->loadReferenceEntity();
        $this->loadUrlAttribute(MediaType::IMAGE);
        $this->loadRecord();

        $attributeRepository = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.attribute');
        $attribute = $attributeRepository->getByIdentifier($this->attributeIdentifier);
        $this->imageGenerator->supports('test', $attribute, ImageGenerator::THUMBNAIL_TYPE);
        $previewImage = $this->imageGenerator->generate('test', $attribute, ImageGenerator::THUMBNAIL_TYPE);

        $this->assertStringContainsString(sprintf('media/cache/%s/pim_enrich_file_misc_default_image', ImageGenerator::THUMBNAIL_TYPE), $previewImage);
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

    private function loadUrlAttribute(string $mediaType)
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
            Prefix::fromString(self::Url),
            Suffix::empty(),
            MediaType::fromString($mediaType)
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
