<?php

namespace Pim\Bundle\CatalogBundle\EventSubscriber\ORM;

use Akeneo\Component\FileStorage\Repository\FileInfoRepositoryInterface;
use Akeneo\Component\StorageUtils\Repository\CachedObjectRepositoryInterface;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\LocaleInterface;
use Pim\Component\Catalog\Model\Metric;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductPrice;
use Pim\Component\Catalog\Model\ProductValueInterface;
use Pim\Component\Catalog\ProductValue;
use Pim\Component\ReferenceData\Repository\ReferenceDataRepositoryResolverInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * TODO: we could use an Entity Listener instead (need to upgrade bundle to 1.3)
 * cf. http://symfony.com/doc/current/bundles/DoctrineBundle/entity-listeners.html
 * cf. http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/reference/events.html#entity-listeners
 */
class LoadProductValuesSubscriber implements EventSubscriber
{
    /** @var ContainerInterface */
    private $container;

    /** @var CachedObjectRepositoryInterface */
    private $attributeRepository;

    /** @var CachedObjectRepositoryInterface */
    private $attributeOptionRepository;

    /** @var CachedObjectRepositoryInterface */
    private $localeRepository;

    /** @var CachedObjectRepositoryInterface */
    private $channelRepository;

    /** @var FileInfoRepositoryInterface */
    private $fileInfoRepository;

    private $referenceDataRepositoryResolver;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function getSubscribedEvents()
    {
        return [
            'postLoad'
        ];
    }

    public function postLoad(LifecycleEventArgs $event)
    {
        $product = $event->getEntity();
        if (!$product instanceof ProductInterface) {
            return;
        }

        $values = [];
        foreach ($product->getRawValues() as $attributeCode => $channelRawValue) {
            $attribute = $this->getAttributeRepository()->findOneByIdentifier($attributeCode);

            foreach ($channelRawValue as $channelCode => $localeRawValue) {
                $channel = $channelCode === '<all_channels>' ? null : $this->getChannelRepository()->findOneByIdentifier($channelCode);

                foreach ($localeRawValue as $localeCode => $data) {
                    $locale = $localeCode === '<all_locales>' ? null : $this->getLocaleRepository()->findOneByIdentifier($localeCode);

                    $values[] = $this->rawValueToValue($attribute, $data, $locale, $channel);
                }
            }
        }

        $product->setValues($values);
    }

    /**
     * @param AttributeInterface    $attribute
     * @param mixed                 $rawData
     * @param LocaleInterface|null  $locale
     * @param ChannelInterface|null $channel
     *
     * @return ProductValueInterface
     * 
     * @throws \Exception
     */
    private function rawValueToValue(
        AttributeInterface $attribute,
        $rawData,
        LocaleInterface $locale = null,
        ChannelInterface $channel = null
    ) {
        //TODO: factory with real ProductValue object
        $value = new ProductValue();
        $value->setAttribute($attribute);
        if (null !== $locale) $value->setLocale($locale);
        if (null !== $channel) $value->setScope($channel);

        switch ($attribute->getAttributeType()) {
            case 'pim_catalog_identifier':
                //TODO: we should drop this as the identifier is now a real column
                //TODO: we keep it now for BC
                $value->setData($rawData);
                break;
            case 'pim_catalog_simpleselect':
                $option = $this->getAttributeOptionRepository()->findOneByIdentifier($attribute->getCode() . "." . $rawData);
                $value->setData($option);
                break;
            case 'pim_catalog_multiselect':
                foreach ($rawData as $rawOption) {
                    $option = $this->getAttributeOptionRepository()->findOneByIdentifier($attribute->getCode() . "." . $rawOption);
                    $value->addOption($option);
                }
                break;
            case 'pim_catalog_text':
            case 'pim_catalog_textarea':
            case 'pim_catalog_boolean':
            case 'pim_catalog_number':
                $value->setData($rawData);
                break;
            case 'pim_catalog_date':
                //TODO: should we keep it as standard format? like for double..
                $date = new \DateTime($rawData);
                $value->setData($date);
                break;
            case 'pim_catalog_file':
            case 'pim_catalog_image':
                $fileInfo = $this->getFileInfoRepository()->findOneByIdentifier($rawData);
                $value->setData($fileInfo);
                break;
            case 'pim_catalog_metric':
                $metric = new Metric();
                $metric->setData($rawData['amount']);
                $metric->setUnit($rawData['unit']);
                $value->setData($metric);
                break;
            case 'pim_catalog_price_collection':
                foreach ($rawData as $rawPrice) {
                    $price = new ProductPrice($rawPrice['amount'], $rawPrice['currency']);
                    $value->addPrice($price);
                }
                break;
            case 'pim_reference_data_simpleselect':
                $repository = $this->getReferenceDataRepositoryResolver()->resolve($attribute->getReferenceDataName());
                $refData = $repository->findOneByIdentifier($rawData);
                $value->setData($refData);
                break;
            case 'pim_reference_data_multiselect':
                $repository = $this->getReferenceDataRepositoryResolver()->resolve($attribute->getReferenceDataName());
                $refData = [];
                foreach ($rawData as $rawRefData) {
                    $oneRefData = $repository->findOneByIdentifier($rawRefData);
                    $refData[] = $oneRefData;
                }
                $value->setData($refData);
                break;
            default:
                throw new \Exception(sprintf('Can not convert a "%s" raw value to a real object.', $attribute->getAttributeType()));
        }


        return $value;
    }

    /**
     * @return CachedObjectRepositoryInterface
     */
    private function getAttributeRepository()
    {
        if (null === $this->attributeRepository) {
            $this->attributeRepository = $this->container->get('pim_catalog.repository.cached_attribute');
        }

        return $this->attributeRepository;
    }

    /**
     * @return CachedObjectRepositoryInterface
     */
    private function getLocaleRepository()
    {
        if (null === $this->localeRepository) {
            $this->localeRepository = $this->container->get('pim_catalog.repository.cached_locale');
        }

        return $this->localeRepository;
    }

    /**
     * @return CachedObjectRepositoryInterface
     */
    private function getChannelRepository()
    {
        if (null === $this->channelRepository) {
            $this->channelRepository = $this->container->get('pim_catalog.repository.cached_channel');
        }

        return $this->channelRepository;
    }

    /**
     * @return CachedObjectRepositoryInterface
     */
    private function getAttributeOptionRepository()
    {
        if (null === $this->attributeOptionRepository) {
            $this->attributeOptionRepository = $this->container->get('pim_catalog.repository.cached_attribute_option');
        }

        return $this->attributeOptionRepository;
    }

    /**
     * @return FileInfoRepositoryInterface
     */
    private function getFileInfoRepository()
    {
        if (null === $this->fileInfoRepository) {
            $this->fileInfoRepository = $this->container->get('akeneo_file_storage.repository.file_info');
        }

        return $this->fileInfoRepository;
    }

    /**
     * @return ReferenceDataRepositoryResolverInterface
     */
    private function getReferenceDataRepositoryResolver()
    {
        if (null === $this->referenceDataRepositoryResolver) {
            $this->referenceDataRepositoryResolver = $this->container->get('pim_reference_data.repository_resolver');
        }

        return $this->referenceDataRepositoryResolver;
    }
}
