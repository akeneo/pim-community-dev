<?php

namespace Pim\Component\Connector\Processor\Denormalization;

use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Component\Catalog\Comparator\Filter\ProductFilterInterface;
use Pim\Component\Connector\ArrayConverter\StandardArrayConverterInterface;
use Symfony\Component\Validator\ValidatorInterface;

/**
 * Product association import processor
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductAssociationProcessor extends AbstractProcessor
{
    /** @var StandardArrayConverterInterface */
    protected $arrayConverter;

    /** @var IdentifiableObjectRepositoryInterface */
    protected $repository;

    /** @var ObjectUpdaterInterface */
    protected $updater;

    /** @var ValidatorInterface */
    protected $validator;

    /** @var ProductFilterInterface */
    private $productAssocFilter;

    /**
     * @param StandardArrayConverterInterface       $arrayConverter    array converter
     * @param IdentifiableObjectRepositoryInterface $repository        product repository
     * @param ObjectUpdaterInterface                $updater           product updater
     * @param ValidatorInterface                    $validator         validator of the object
     * @param ProductFilterInterface                $productAssocFilter product association filter
     */
    public function __construct(
        StandardArrayConverterInterface $arrayConverter,
        IdentifiableObjectRepositoryInterface $repository,
        ObjectUpdaterInterface $updater,
        ValidatorInterface $validator,
        ProductFilterInterface $productAssocFilter
    ) {
        parent::__construct($repository);

        $this->arrayConverter     = $arrayConverter;
        $this->repository         = $repository;
        $this->updater            = $updater;
        $this->validator          = $validator;
        $this->productAssocFilter = $productAssocFilter;
    }

    /**
     * {@inheritdoc}
     */
    public function process($item)
    {
        $identifier = $this->getIdentifier($item);
        $product = $this->findProduct($identifier);
        if (!$product) {
            $this->skipItemWithMessage($item, sprintf('No product with identifier "%s" has been found', $identifier));
        }

        $convertedItem = $this->convertItemData($item);
        $convertedItem = $this->filterIdenticalData($product, $convertedItem);

        if (empty($convertedItem)) {
            $this->stepExecution->incrementSummaryInfo('skip');

            return null;
        }

        try {
            $this->updateProduct($product, $convertedItem);
        } catch (\InvalidArgumentException $exception) {
            $this->skipItemWithMessage($item, $exception->getMessage(), $exception);
        }

        $violations = $this->validateProductAssociations($product);
        if ($violations && $violations->count() > 0) {
            $this->skipItemWithConstraintViolations($item, $violations);
        }

        return $product;
    }

    /**
     * @param ProductInterface $product
     * @param array            $convertedItem
     *
     * @return array
     */
    protected function filterIdenticalData(ProductInterface $product, array $convertedItem)
    {
        return $this->productAssocFilter->filter($product, $convertedItem);
    }

    /**
     * @param array $item
     *
     * @return array
     */
    protected function convertItemData(array $item)
    {
        $items = $this->arrayConverter->convert($item);
        $associations = isset($items['associations']) ? $items['associations'] : [];

        return ['associations' => $associations];
    }

    /**
     * @param ProductInterface $product
     * @param array            $convertItems
     *
     * @throws \InvalidArgumentException
     */
    protected function updateProduct(ProductInterface $product, array $convertItems)
    {
        $this->updater->update($product, $convertItems);
    }

    /**
     * @param array $convertedItem
     *
     * @return string
     */
    protected function getIdentifier(array $convertedItem)
    {
        $identifierProperty = $this->repository->getIdentifierProperties();

        return $convertedItem[$identifierProperty[0]];
    }

    /**
     * @param string $identifier
     *
     * @return ProductInterface|null
     */
    public function findProduct($identifier)
    {
        return $this->repository->findOneByIdentifier($identifier);
    }

    /**
     * @param ProductInterface $product
     *
     * @throws \InvalidArgumentException
     *
     * @return \Symfony\Component\Validator\ConstraintViolationListInterface|null
     */
    protected function validateProductAssociations(ProductInterface $product)
    {
        $associations = $product->getAssociations();
        foreach ($associations as $association) {
            $violations = $this->validator->validate($association);
            if ($violations->count() > 0) {
                return $violations;
            }
        }

        return null;
    }
}
