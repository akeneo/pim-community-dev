<?php

namespace Pim\Component\Catalog\Updater;

use Akeneo\Component\StorageUtils\Exception\ImmutablePropertyException;
use Akeneo\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Util\ClassUtils;
use Pim\Component\Catalog\Factory\ProductValueFactory;
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
class VariantGroupUpdater implements ObjectUpdaterInterface
{
    /** @var AttributeRepositoryInterface */
    protected $attributeRepository;

    /** @var GroupTypeRepositoryInterface */
    protected $groupTypeRepository;

    /** @var ProductValueFactory */
    protected $productValueFactory;

    /** @var ProductQueryBuilderFactoryInterface */
    protected $productQueryBuilderFactory;

    /** @var string */
    protected $productTemplateClass;

    /**
     * @param AttributeRepositoryInterface        $attributeRepository
     * @param GroupTypeRepositoryInterface        $groupTypeRepository
     * @param ProductValueFactory                 $productValueFactory
     * @param ProductQueryBuilderFactoryInterface $productQueryBuilderFactory
     * @param string                              $productTemplateClass
     */
    public function __construct(
        AttributeRepositoryInterface $attributeRepository,
        GroupTypeRepositoryInterface $groupTypeRepository,
        ProductValueFactory $productValueFactory,
        ProductQueryBuilderFactoryInterface $productQueryBuilderFactory,
        $productTemplateClass
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->groupTypeRepository = $groupTypeRepository;
        $this->productValueFactory = $productValueFactory;
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
        $mergedValues = $this->mergeValues($originalValues, $newValues);

        $template->setValues($mergedValues);

        $variantGroup->setProductTemplate($template);
    }

    /**
     * Merges original and new values (keeping original ones if missing in the new ones)
     * Iterates on every new attribute and then on every localized and/or scoped value to compare it
     * with the original values.
     *
     * New values respect the standard format:
     *
     * $newValues = [
     *     'code'        => [
     *         [
     *             'locale' => null,
     *             'scope'  => null,
     *             'data'   => 'a_unique_code',
     *         ],
     *     ],
     *     'description' => [
     *         [
     *             'locale' => 'en_US',
     *             'scope'  => 'ecommerce',
     *             'data'   => 'A new description in english',
     *         ],
     *         [
     *             'locale' => 'de_DE',
     *             'scope'  => 'ecommerce',
     *             'data'   => 'Eine neue deutsche Beschreibung',
     *         ],
     *     ]
     * ];
     *
     * @param ProductValueCollectionInterface $values
     * @param array                           $newValues
     *
     * @return ProductValueCollectionInterface
     */
    protected function mergeValues(ProductValueCollectionInterface $values, array $newValues)
    {
        foreach ($newValues as $attributeCode => $newValueArray) {
            $attribute = $this->attributeRepository->findOneByIdentifier($attributeCode);

            foreach ($newValueArray as $newValue) {
                $key = sprintf(
                    '%s-%s-%s',
                    $attributeCode,
                    null !== $newValue['scope'] ? $newValue['scope'] : '<all_channels>',
                    null !== $newValue['locale'] ? $newValue['locale'] : '<all_locales>'
                );

                if ($values->containsKey($key)) {
                    $values->removeKey($key);
                }

                $values->add($this->productValueFactory->create(
                    $attribute,
                    $newValue['scope'],
                    $newValue['locale'],
                    $newValue['data']
                ));
            }
        }

        return $values;
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
     * @param array          $productIds
     */
    protected function setProducts(GroupInterface $variantGroup, array $productIds)
    {
        foreach ($variantGroup->getProducts() as $product) {
            $variantGroup->removeProduct($product);
        }

        if (empty($productIds)) {
            return;
        }

        $pqb = $this->productQueryBuilderFactory->create();
        $pqb->addFilter('id', 'IN', $productIds);

        $products = $pqb->execute();

        foreach ($products as $product) {
            $variantGroup->addProduct($product);
        }
    }
}
