<?php

namespace Pim\Component\Catalog\Updater;

use Akeneo\Component\FileStorage\File\FileStorerInterface;
use Akeneo\Component\FileStorage\Model\FileInfoInterface;
use Akeneo\Component\FileStorage\Repository\FileInfoRepositoryInterface;
use Akeneo\Component\StorageUtils\Exception\ImmutablePropertyException;
use Akeneo\Component\StorageUtils\Exception\InvalidPropertyException;
use Doctrine\Common\Collections\Collection;
use Pim\Component\Catalog\AttributeTypes;
use Pim\Component\Catalog\Factory\ProductValueFactory;
use Pim\Component\Catalog\FileStorage;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\GroupInterface;
use Pim\Component\Catalog\Model\ProductTemplateInterface;
use Pim\Component\Catalog\Model\ProductValueCollection;
use Pim\Component\Catalog\Model\ProductValueCollectionInterface;
use Pim\Component\Catalog\Query\ProductQueryBuilderFactoryInterface;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
use Pim\Component\Catalog\Repository\GroupTypeRepositoryInterface;

/**
 * Updates and validates a variant group
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class VariantGroupUpdater extends GroupUpdater
{
    /** @var ProductValueFactory */
    protected $productValueFactory;

    /** @var FileInfoRepositoryInterface */
    protected $fileInfoRepository;

    /** @var FileStorerInterface */
    protected $fileStorer;

    /** @var string */
    protected $productTemplateClass;

    /**
     * @param AttributeRepositoryInterface        $attributeRepository
     * @param GroupTypeRepositoryInterface        $groupTypeRepository
     * @param ProductValueFactory                 $productValueFactory
     * @param FileInfoRepositoryInterface         $fileInfoRepository
     * @param FileStorerInterface                 $fileStorer
     * @param ProductQueryBuilderFactoryInterface $productQueryBuilderFactory
     * @param string                              $productTemplateClass
     */
    public function __construct(
        AttributeRepositoryInterface $attributeRepository,
        GroupTypeRepositoryInterface $groupTypeRepository,
        ProductValueFactory $productValueFactory,
        FileInfoRepositoryInterface $fileInfoRepository,
        FileStorerInterface $fileStorer,
        ProductQueryBuilderFactoryInterface $productQueryBuilderFactory,
        $productTemplateClass
    ) {
        parent::__construct($groupTypeRepository, $attributeRepository, $productQueryBuilderFactory);

        $this->productValueFactory = $productValueFactory;
        $this->fileInfoRepository = $fileInfoRepository;
        $this->fileStorer = $fileStorer;
        $this->productTemplateClass = $productTemplateClass;
    }

    /**
     * @param GroupInterface $variantGroup
     * @param string         $field
     * @param mixed          $data
     *
     * @throws InvalidPropertyException
     * @throws ImmutablePropertyException
     */
    protected function setData(GroupInterface $variantGroup, $field, $data)
    {
        switch ($field) {
            case 'axes':
                $this->setAxes($variantGroup, $data);
                break;
            case 'values':
                $this->setValues($variantGroup, $data);
                break;
            default:
                parent::setData($variantGroup, $field, $data);
        }
    }

    /**
     * @param GroupInterface $variantGroup
     * @param string         $type
     *
     * @throws InvalidPropertyException
     */
    protected function setType(GroupInterface $variantGroup, $type)
    {
        $groupType = $this->groupTypeRepository->findOneByIdentifier($type);
        if (null !== $groupType) {
            $variantGroup->setType($groupType);
        } else {
            throw InvalidPropertyException::validEntityCodeExpected(
                'type',
                'group type',
                'The group type does not exist',
                static::class,
                $type
            );
        }
    }

    /**
     * @param GroupInterface $variantGroup
     * @param array          $axes
     *
     * @throws ImmutablePropertyException
     * @throws InvalidPropertyException
     */
    protected function setAxes(GroupInterface $variantGroup, array $axes)
    {
        if (null !== $variantGroup->getId()) {
            if (array_diff($this->getOriginalAxes($variantGroup->getAxisAttributes()), array_values($axes))) {
                throw ImmutablePropertyException::immutableProperty(
                    'axes',
                    implode(',', $axes),
                    static::class
                );
            }
        }

        foreach ($axes as $axis) {
            $attribute = $this->getAttributeOrThrowException($axis);

            $variantGroup->addAxisAttribute($attribute);
        }
    }

    /**
     * @param GroupInterface $variantGroup
     * @param array          $newValues
     */
    protected function setValues(GroupInterface $variantGroup, array $newValues)
    {
        $template = $this->getProductTemplate($variantGroup);
        $templateProductValues = $template->getValues();

        if (null === $templateProductValues) {
            $templateProductValues = new ProductValueCollection();
        }
        $mergedValues = $this->updateTemplateValues($templateProductValues, $newValues);

        $template->setValues($mergedValues);

        $variantGroup->setProductTemplate($template);
    }

    /**
     * Update the values of the variant group product template.
     *
     * New values respect the standard format, so we can use the product updater
     * on a temporary product.
     *
     * @param ProductValueCollectionInterface $templateProductValues
     * @param array                           $newValues
     *
     * @return ProductValueCollectionInterface
     */
    protected function updateTemplateValues(ProductValueCollectionInterface $templateProductValues, array $newValues)
    {
        foreach ($newValues as $attributeCode => $newValue) {
            $attribute = $this->getAttributeOrThrowException($attributeCode);

            foreach ($newValue as $standardValue) {
                if (AttributeTypes::BACKEND_TYPE_MEDIA === $attribute->getBackendType()) {
                    $file = $this->fileInfoRepository->findOneByIdentifier($standardValue['data']);
                    if (null === $file) {
                        $file = $this->storeFile($attribute, $standardValue['data']);
                    }

                    $standardValue['data'] = $file->getKey();
                }

                $newProductValue = $this->productValueFactory->create(
                    $attribute,
                    $standardValue['scope'],
                    $standardValue['locale'],
                    $standardValue['data']
                );

                $templateProductValues->add($newProductValue);
            }
        }

        return $templateProductValues;
    }

    /**
     * TODO: inform the user that this could take some time.
     *
     * @param AttributeInterface $attribute
     * @param string             $data
     *
     * @throws InvalidPropertyException If an invalid filePath is provided
     * @return FileInfoInterface|null
     */
    protected function storeFile(AttributeInterface $attribute, $data)
    {
        if (null === $data) {
            return null;
        }

        $rawFile = new \SplFileInfo($data);

        if (!$rawFile->isFile()) {
            throw InvalidPropertyException::validPathExpected(
                $attribute->getCode(),
                static::class,
                $data
            );
        }

        $file = $this->fileStorer->store($rawFile, FileStorage::CATALOG_STORAGE_ALIAS);

        return $file;
    }

    /**
     * @param GroupInterface $variantGroup
     *
     * @return ProductTemplateInterface
     */
    protected function getProductTemplate(GroupInterface $variantGroup)
    {
        if ($variantGroup->getProductTemplate()) {
            $template = $variantGroup->getProductTemplate();
        } else {
            $template = new $this->productTemplateClass();
        }

        return $template;
    }

    /**
     * @param Collection $axes
     *
     * @return array
     */
    protected function getOriginalAxes(Collection $axes)
    {
        $data = [];
        foreach ($axes as $axis) {
            $data[] = $axis->getCode();
        }

        return $data;
    }

    /**
     * @param string $attributeCode
     *
     * @return AttributeInterface
     */
    protected function getAttributeOrThrowException($attributeCode)
    {
        $attribute = $this->attributeRepository->findOneByIdentifier($attributeCode);

        if (null === $attribute) {
            throw InvalidPropertyException::validEntityCodeExpected(
                'axes',
                'attribute code',
                'The attribute does not exist',
                static::class,
                $attributeCode
            );
        }

        return $attribute;
    }
}
