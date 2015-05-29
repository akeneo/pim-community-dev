<?php

namespace Pim\Bundle\CatalogBundle\Updater;

use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Util\ClassUtils;
use Pim\Bundle\CatalogBundle\Builder\ProductBuilderInterface;
use Pim\Bundle\CatalogBundle\Model\GroupInterface;
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
     * @param array          $arrayValues
     */
    public function setValues(GroupInterface $variantGroup, array $arrayValues)
    {
        $values = $this->transformArrayToValues($arrayValues);

        // TODO: remove it when normalizers & setters will be uniformized (PIM-4246)
        foreach ($arrayValues as $code => $data) {
            foreach ($data as $index => $value) {
                $arrayValues[$code][$index]['value'] = $value['data'];
                unset($arrayValues[$code][$index]['data']);
            }
        }

        $template = $this->getProductTemplate($variantGroup);
        $template->setValues($values);
        $template->setValuesData($arrayValues);

        $variantGroup->setProductTemplate($template);
    }

    /**
     * Transform an array of values to ProductValues
     *
     * @param array $arrayValues
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
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
     * @return \Pim\Bundle\CatalogBundle\Model\ProductTemplateInterface
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
}
