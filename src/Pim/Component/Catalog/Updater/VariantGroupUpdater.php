<?php

namespace Pim\Component\Catalog\Updater;

use Akeneo\Component\StorageUtils\Exception\ImmutablePropertyException;
use Akeneo\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Util\ClassUtils;
use Pim\Component\Catalog\Builder\ProductBuilderInterface;
use Pim\Component\Catalog\Model\GroupInterface;
use Pim\Component\Catalog\Model\ProductTemplateInterface;
use Pim\Component\Catalog\Model\ProductValueCollection;
use Pim\Component\Catalog\Model\ProductValueCollectionInterface;
use Pim\Component\Catalog\Query\Filter\Operators;
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
class VariantGroupUpdater implements ObjectUpdaterInterface
{
    /** @var AttributeRepositoryInterface */
    protected $attributeRepository;

    /** @var GroupTypeRepositoryInterface */
    protected $groupTypeRepository;

    /** @var ProductBuilderInterface */
    protected $productBuilder;

    /** @var ObjectUpdaterInterface */
    protected $productUpdater;

    /** @var ProductQueryBuilderFactoryInterface */
    protected $productQueryBuilderFactory;

    /** @var string */
    protected $productTemplateClass;

    /**
     * @param AttributeRepositoryInterface        $attributeRepository
     * @param GroupTypeRepositoryInterface        $groupTypeRepository
     * @param ProductBuilderInterface             $productBuilder
     * @param ObjectUpdaterInterface              $productUpdater
     * @param ProductQueryBuilderFactoryInterface $productQueryBuilderFactory
     * @param string                              $productTemplateClass
     */
    public function __construct(
        AttributeRepositoryInterface $attributeRepository,
        GroupTypeRepositoryInterface $groupTypeRepository,
        ProductBuilderInterface $productBuilder,
        ObjectUpdaterInterface $productUpdater,
        ProductQueryBuilderFactoryInterface $productQueryBuilderFactory,
        $productTemplateClass
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->groupTypeRepository = $groupTypeRepository;
        $this->productBuilder = $productBuilder;
        $this->productUpdater = $productUpdater;
        $this->productQueryBuilderFactory = $productQueryBuilderFactory;
        $this->productTemplateClass = $productTemplateClass;
    }

    /**
     * {@inheritdoc}
     *
     * Expected input format :
     * {
     *     "code": "mycode",
     *     "labels": {
     *         "en_US": "T-shirt very beautiful",
     *         "fr_FR": "T-shirt super beau"
     *     }
     *     "axes": ["main_color", "secondary_color"],
     *     "type": "VARIANT",
     *     "values": {
     *         "main_color": "white",
     *         "tshirt_style": ["turtleneck","sportwear"],
     *         "description": [
     *              {
     *                  "locale": "fr_FR",
     *                  "scope": "ecommerce",
     *                  "data": "<p>description</p>",
     *              },
     *              {
     *                  "locale": "en_US",
     *                  "scope": "ecommerce",
     *                  "data": "<p>description</p>",
     *              }
     *          ]
     *     }
     * }
     */
    public function update($variantGroup, array $data, array $options = [])
    {
        if (!$variantGroup instanceof GroupInterface) {
            throw InvalidObjectException::objectExpected(
                ClassUtils::getClass($variantGroup),
                GroupInterface::class
            );
        }

        foreach ($data as $field => $item) {
            $this->setData($variantGroup, $field, $item);
        }

        return $this;
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
            case 'code':
                $this->setCode($variantGroup, $data);
                break;

            case 'type':
                $this->setType($variantGroup, $data);
                break;

            case 'labels':
                $this->setLabels($variantGroup, $data);
                break;

            case 'axes':
                $this->setAxes($variantGroup, $data);
                break;

            case 'values':
                $this->setValues($variantGroup, $data);
                break;

            case 'products':
                $this->setProducts($variantGroup, $data);
                break;
        }
    }

    /**
     * @param GroupInterface $variantGroup
     * @param string         $code
     */
    protected function setCode(GroupInterface $variantGroup, $code)
    {
        $variantGroup->setCode($code);
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
     * @param array          $labels
     */
    protected function setLabels(GroupInterface $variantGroup, array $labels)
    {
        foreach ($labels as $localeCode => $label) {
            $variantGroup->setLocale($localeCode);
            $translation = $variantGroup->getTranslation();
            $translation->setLabel($label);
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
                    static::class,
                    'variant group'
                );
            }
        }

        foreach ($axes as $axis) {
            $attribute = $this->attributeRepository->findOneByIdentifier($axis);
            if (null !== $attribute) {
                $variantGroup->addAxisAttribute($attribute);
            } else {
                throw InvalidPropertyException::validEntityCodeExpected(
                    'axes',
                    'attribute code',
                    'The attribute does not exist',
                    static::class,
                    $axis
                );
            }
        }
    }

    /**
     * @param GroupInterface $variantGroup
     * @param array          $newValues
     */
    protected function setValues(GroupInterface $variantGroup, array $newValues)
    {
        $template = $this->getProductTemplate($variantGroup);
        $originalValues = $template->getValues();

        if (null === $originalValues) {
            $originalValues = new ProductValueCollection();
        }
        $mergedValues = $this->updateTemplateValues($originalValues, $newValues);

        $template->setValues($mergedValues);

        $variantGroup->setProductTemplate($template);
    }

    /**
     * Update the values of the variant group product template.
     *
     * New values respect the standard format, so we can use the product updater
     * on a temporary product.
     *
     * @param ProductValueCollectionInterface $values
     * @param array                           $newValues
     *
     * @return ProductValueCollectionInterface
     */
    protected function updateTemplateValues(ProductValueCollectionInterface $values, array $newValues)
    {
        $product = $this->productBuilder->createProduct();
        $product->setValues($values);

        $this->productUpdater->update($product, ['values' => $newValues]);

        return $product->getValues();
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
     * @param GroupInterface $variantGroup
     * @param array          $productIdentifiers
     */
    protected function setProducts(GroupInterface $variantGroup, array $productIdentifiers)
    {
        foreach ($variantGroup->getProducts() as $product) {
            $variantGroup->removeProduct($product);
        }

        if (empty($productIdentifiers)) {
            return;
        }

        $pqb = $this->productQueryBuilderFactory->create();
        $pqb->addFilter('identifier', Operators::IN_LIST, $productIdentifiers);

        $products = $pqb->execute();

        foreach ($products as $product) {
            $variantGroup->addProduct($product);
        }
    }
}
