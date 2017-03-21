<?php

namespace Pim\Component\Catalog\Updater;

use Akeneo\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Doctrine\Common\Util\ClassUtils;
use Pim\Component\Catalog\Model\GroupInterface;
use Pim\Component\Catalog\Query\ProductQueryBuilderFactoryInterface;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
use Pim\Component\Catalog\Repository\GroupTypeRepositoryInterface;

/**
 * Updates and validates a group
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GroupUpdater implements ObjectUpdaterInterface
{
    /** @var GroupTypeRepositoryInterface */
    protected $groupTypeRepository;

    /** @var AttributeRepositoryInterface */
    protected $attributeRepository;

    /** @var ProductQueryBuilderFactoryInterface */
    protected $productQueryBuilderFactory;

    /**
     * @param GroupTypeRepositoryInterface        $groupTypeRepository
     * @param AttributeRepositoryInterface        $attributeRepository
     * @param ProductQueryBuilderFactoryInterface $productQueryBuilderFactory
     */
    public function __construct(
        GroupTypeRepositoryInterface $groupTypeRepository,
        AttributeRepositoryInterface $attributeRepository,
        ProductQueryBuilderFactoryInterface $productQueryBuilderFactory
    ) {
        $this->groupTypeRepository = $groupTypeRepository;
        $this->attributeRepository = $attributeRepository;
        $this->productQueryBuilderFactory = $productQueryBuilderFactory;
    }

    /**
     * {@inheritdoc}
     *
     * Expected input format :
     * [
     *     'code'   => 'mycode',
     *     'labels' => [
     *         'en_US' => 'T-shirt very beautiful',
     *         'fr_FR' => 'T-shirt super beau'
     *     ],
     *     'axis'   => ['size', 'color']
     * ]
     */
    public function update($group, array $data, array $options = [])
    {
        if (!$group instanceof GroupInterface) {
            throw InvalidObjectException::objectExpected(
                ClassUtils::getClass($group),
                'Pim\Component\Catalog\Model\GroupInterface'
            );
        }

        foreach ($data as $field => $item) {
            $this->setData($group, $field, $item);
        }

        return $this;
    }

    /**
     * @param GroupInterface $group
     * @param string         $field
     * @param mixed          $data
     *
     * @throws InvalidPropertyException
     */
    protected function setData(GroupInterface $group, $field, $data)
    {
        switch ($field) {
            case 'code':
                $this->setCode($group, $data);
                break;
            case 'type':
                $this->setType($group, $data);
                break;
            case 'labels':
                $this->setLabels($group, $data);
                break;
            case 'axis':
                $this->setAxis($group, $data);
                break;
            case 'products':
                $this->setProducts($group, $data);
                break;
        }
    }

    /**
     * @param GroupInterface $group
     * @param string         $code
     */
    protected function setCode(GroupInterface $group, $code)
    {
        $group->setCode($code);
    }

    /**
     * @param GroupInterface $group
     * @param string         $type
     *
     * @throws InvalidPropertyException
     */
    protected function setType(GroupInterface $group, $type)
    {
        $groupType = $this->groupTypeRepository->findOneByIdentifier($type);

        if (null === $groupType) {
            throw InvalidPropertyException::validEntityCodeExpected(
                'type',
                'group type',
                'The group type does not exist',
                static::class,
                $type
            );
        }

        if ($groupType->isVariant()) {
            throw InvalidPropertyException::validGroupTypeExpected(
                'type',
                'Cannot process variant group, only groups are supported',
                static::class,
                $group->getCode()
            );
        }

        $group->setType($groupType);
    }

    /**
     * @param GroupInterface $group
     * @param array          $labels
     */
    protected function setLabels(GroupInterface $group, array $labels)
    {
        foreach ($labels as $localeCode => $label) {
            $group->setLocale($localeCode);
            $translation = $group->getTranslation();
            $translation->setLabel($label);
        }
    }

    /**
     * @param GroupInterface $group
     * @param string[]       $attributeCodes
     *
     * @throws InvalidPropertyException
     */
    protected function setAxis(GroupInterface $group, array $attributeCodes)
    {
        $attributes = [];
        foreach ($attributeCodes as $attributeCode) {
            $attribute = $this->attributeRepository->findOneByIdentifier($attributeCode);
            if (null === $attribute) {
                throw InvalidPropertyException::validEntityCodeExpected(
                    'axis',
                    'attribute code',
                    'The attribute does not exist',
                    static::class,
                    $attributeCode
                );
            }
            $attributes[] = $attribute;
        }
        $group->setAxisAttributes($attributes);
    }

    /**
     * @param GroupInterface $group
     * @param array          $productIds
     */
    protected function setProducts(GroupInterface $group, array $productIds)
    {
        foreach ($group->getProducts() as $product) {
            $group->removeProduct($product);
        }

        if (empty($productIds)) {
            return;
        }

        $pqb = $this->productQueryBuilderFactory->create();
        $pqb->addFilter('id', 'IN', $productIds);

        $products = $pqb->execute();

        foreach ($products as $product) {
            $group->addProduct($product);
        }
    }
}
