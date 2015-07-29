<?php

namespace Pim\Component\Catalog\Updater;

use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Util\ClassUtils;
use Pim\Bundle\CatalogBundle\Builder\ProductBuilderInterface;
use Pim\Bundle\CatalogBundle\Model\GroupInterface;
use Pim\Bundle\CatalogBundle\Model\ProductTemplateInterface;
use Pim\Bundle\CatalogBundle\Repository\AttributeRepositoryInterface;
use Pim\Bundle\CatalogBundle\Repository\GroupTypeRepositoryInterface;

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

    /** @var string */
    protected $productTemplateClass;

    /**
     * @param AttributeRepositoryInterface $attributeRepository
     * @param GroupTypeRepositoryInterface $groupTypeRepository
     * @param ProductBuilderInterface      $productBuilder
     * @param ObjectUpdaterInterface       $productUpdater
     * @param string                       $productTemplateClass
     */
    public function __construct(
        AttributeRepositoryInterface $attributeRepository,
        GroupTypeRepositoryInterface $groupTypeRepository,
        ProductBuilderInterface $productBuilder,
        ObjectUpdaterInterface $productUpdater,
        $productTemplateClass
    ) {
        $this->attributeRepository  = $attributeRepository;
        $this->groupTypeRepository  = $groupTypeRepository;
        $this->productBuilder       = $productBuilder;
        $this->productUpdater       = $productUpdater;
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
     *     "axis": ["main_color", "secondary_color"],
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
            throw new \InvalidArgumentException(
                sprintf(
                    'Expects a "Pim\Bundle\CatalogBundle\Model\GroupInterface", "%s" provided.',
                    ClassUtils::getClass($variantGroup)
                )
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
     * @throws \InvalidArgumentException
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

            case 'axis':
                $this->setAxes($variantGroup, $data);
                break;

            case 'values':
                $this->setValues($variantGroup, $data);
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
     * @throws \InvalidArgumentException
     */
    protected function setType(GroupInterface $variantGroup, $type)
    {
        $groupType = $this->groupTypeRepository->findOneByIdentifier($type);
        if (null !== $groupType) {
            $variantGroup->setType($groupType);
        } else {
            throw new \InvalidArgumentException(sprintf('Type "%s" does not exist', $type));
        }
    }

    /**
     * @param GroupInterface $variantGroup
     * @param array          $labels
     *
     * @throws \InvalidArgumentException
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
     * @throws \InvalidArgumentException
     */
    protected function setAxes(GroupInterface $variantGroup, array $axes)
    {
        if (null !== $variantGroup->getId()) {
            if (array_diff($this->getOriginalAxes($variantGroup->getAxisAttributes()), array_values($axes))) {
                throw new \InvalidArgumentException('Attributes: This property cannot be changed.');
            }
        }

        foreach ($axes as $axis) {
            $attribute = $this->attributeRepository->findOneByIdentifier($axis);
            if (null !== $attribute) {
                $variantGroup->addAxisAttribute($attribute);
            } else {
                throw new \InvalidArgumentException(sprintf('Attribute "%s" does not exist', $axis));
            }
        }
    }

    /**
     * @param GroupInterface $variantGroup
     * @param array          $newValues
     */
    public function setValues(GroupInterface $variantGroup, array $newValues)
    {
        $template = $this->getProductTemplate($variantGroup);
        $originalValues = $template->getValuesData();
        $mergedValuesData = $this->mergeValuesData($originalValues, $newValues);
        $mergedValues = $this->transformArrayToValues($mergedValuesData);
        $mergedValuesData = $this->replaceMediaLocalPathsByStoredPaths($mergedValues, $mergedValuesData);

        $template->setValues($mergedValues);
        $template->setValuesData($mergedValuesData);

        $variantGroup->setProductTemplate($template);
    }

    /**
     * Transform an array of values to ProductValues
     *
     * @param array $arrayValues
     *
     * @return ArrayCollection
     */
    protected function transformArrayToValues(array $arrayValues)
    {
        $product = $this->productBuilder->createProduct();
        $this->productUpdater->update($product, $arrayValues);

        $values = $product->getValues();
        $values->removeElement($product->getIdentifier());

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
     * Merge new values in original values
     *
     * @param array $originalValues
     * @param array $newValues
     *
     * @return array
     */
    protected function mergeValuesData(array $originalValues, array $newValues)
    {
        foreach ($newValues as $code => $values) {
            if (!isset($originalValues[$code])) {
                $originalValues[$code] = $values;
            } else {
                foreach ($values as $newValue) {
                    $newKey = $code;
                    $newKey .= isset($value['locale']) ? '-' . $value['locale'] : '';
                    $newKey .= isset($value['scope']) ? '-' . $value['scope'] : '';
                    foreach ($originalValues[$code] as $currentIndex => $currentValue) {
                        $currentKey = $code;
                        $currentKey .= isset($value['locale']) ? '-' . $value['locale'] : '';
                        $currentKey .= isset($value['scope']) ? '-' . $value['scope'] : '';
                        if ($newKey === $currentKey) {
                            unset($originalValues[$code][$currentIndex]);
                        }
                    }
                    $originalValues[$code][] = $newValue;
                }
            }
        }

        return $originalValues;
    }

    /**
     * Replace media local paths by stored paths in the merged values data as
     * the file has already been stored during the construction of the product values
     * (in the method transformArrayToValues).
     *
     * @param Collection $mergedValues
     * @param array      $mergedValuesData
     *
     * @return array
     */
    protected function replaceMediaLocalPathsByStoredPaths(Collection $mergedValues, array $mergedValuesData)
    {
        foreach ($mergedValues as $value) {
            if (null !== $value->getMedia()) {
                $attributeCode = $value->getAttribute()->getCode();
                foreach ($mergedValuesData[$attributeCode] as $index => $data) {
                    $mergedValuesData[$attributeCode][$index]['data']['filePath'] = $value->getMedia()->getKey();
                }
            }
        }

        return $mergedValuesData;
    }
}
