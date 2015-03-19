<?php

namespace Pim\Bundle\BaseConnectorBundle\Processor\Denormalization;

use Akeneo\Bundle\StorageUtilsBundle\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use Pim\Bundle\BaseConnectorBundle\Processor\Denormalization\Converter\StandardArrayConverterInterface;
use Pim\Bundle\CatalogBundle\Builder\ProductBuilderInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Updater\ProductUpdaterInterface;
use Symfony\Component\Validator\ValidatorInterface;

/**
 * Product import processor
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductProcessor extends AbstractReworkedProcessor
{
    /** @var StandardArrayConverterInterface */
    protected $arrayConverter;

    /** @var ProductBuilderInterface */
    protected $productBuilder;

    /** @var ProductUpdaterInterface */
    protected $productUpdater;

    /**
     * @param StandardArrayConverterInterface       $arrayConverter array converter
     * @param IdentifiableObjectRepositoryInterface $repository     product repository
     * @param ProductBuilderInterface               $productBuilder product builder
     * @param ProductUpdaterInterface               $productUpdater product updater
     * @param ValidatorInterface                    $validator      validator of the object
     * @param ObjectDetacherInterface               $detacher       detacher to remove it from UOW when skip
     */
    public function __construct(
        StandardArrayConverterInterface $arrayConverter,
        IdentifiableObjectRepositoryInterface $repository,
        ProductBuilderInterface $productBuilder,
        ProductUpdaterInterface $productUpdater,
        ValidatorInterface $validator,
        ObjectDetacherInterface $detacher
    ) {
        parent::__construct($repository, $validator, $detacher);

        $this->productBuilder = $productBuilder;
        $this->arrayConverter      = $arrayConverter;
        $this->productUpdater = $productUpdater;
    }

    /**
     * {@inheritdoc}
     */
    public function process($item)
    {
        $convertedItem = $this->convertItemData($item);
        $identifier    = $this->getIdentifier($convertedItem);
        $familyCode    = $this->getFamilyCode($convertedItem);
        $filteredItem  = $this->filterItemData($convertedItem);

        $product = $this->findOrCreateProduct($identifier, $familyCode);
        $this->updateProduct($product, $filteredItem);
        $this->validateProduct($product, $item);

        return $product;
    }

    /**
     * @param array $item
     *
     * @return array
     */
    protected function convertItemData(array $item)
    {
        return $this->arrayConverter->convert($item);
    }

    /**
     * @param array $convertedItem
     *
     * @return string
     */
    protected function getIdentifier(array $convertedItem)
    {
        $identifierProperty = $this->repository->getIdentifierProperties();

        return $convertedItem[$identifierProperty[0]][0]['data'];
    }

    /**
     * @param array $convertedItem
     *
     * @return string|null
     */
    protected function getFamilyCode(array $convertedItem)
    {
        return $convertedItem['family'];
    }

    /**
     * @param array $convertedItem
     *
     * @return array
     */
    protected function filterItemData(array $convertedItem)
    {
        unset($convertedItem[$this->repository->getIdentifierProperties()[0]]);
        unset($convertedItem['associations']);
        unset($convertedItem['family']);
        unset($convertedItem['groups']); // TODO: until we split groups and variant groups

        return $convertedItem;
    }

    /**
     * @param string $identifier
     * @param string $familyCode
     *
     * @return ProductInterface
     */
    protected function findOrCreateProduct($identifier, $familyCode)
    {
        $product = $this->repository->findOneByIdentifier($identifier);
        if (false === $product) {
            $product = $this->productBuilder->createProduct($identifier, $familyCode);
        }

        return $product;
    }

    /**
     * @param ProductInterface $product
     * @param array            $item
     */
    protected function updateProduct(ProductInterface $product, array $item)
    {
        foreach ($item as $field => $values) {
            if (in_array($field, ['enabled', 'categories', 'groups'])) {
                $this->productUpdater->setData($product, $field, $values, []);
            } else {
                foreach ($values as $value) {
                    // sets the value if the attribute belongs to the family or if the value already exists as optional
                    $family = $product->getFamily();
                    $belongsToFamily = $family === null ? false : $family->hasAttributeCode($field);
                    $hasValue = $product->getValue($field, $value['locale'], $value['scope']) !== null;
                    if ($belongsToFamily || $hasValue) {
                        $options = ['locale' => $value['locale'], 'scope' => $value['scope']];
                        $this->productUpdater->setData($product, $field, $value['data'], $options);
                    }
                }
            }
        }
    }

    /**
     * @param ProductInterface $product
     * @param array            $item
     */
    protected function validateProduct(ProductInterface $product, array $item)
    {
        $violations = $this->validator->validate($product);
        if ($violations->count() !== 0) {
            $this->detachObject($product);
            $this->skipItemWithConstraintViolations($item, $violations);
        }
    }
}
