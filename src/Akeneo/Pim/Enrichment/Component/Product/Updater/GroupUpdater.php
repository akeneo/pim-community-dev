<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Updater;

use Akeneo\Pim\Enrichment\Component\Product\Model\GroupInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\GroupTranslationInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Pim\Structure\Component\Repository\GroupTypeRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Doctrine\Common\Util\ClassUtils;
use Webmozart\Assert\Assert;

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
                GroupInterface::class
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
            Assert::implementsInterface($translation, GroupTranslationInterface::class);
            $translation->setLabel($label);
        }
    }

    /**
     * @todo Find a better solution than a database query to determine what are the products that have been added.
     *       (it will certainly cause a BC-break)
     */
    protected function setProducts(GroupInterface $group, array $productIdentifiers)
    {
        $oldProductIdentifiers = [];
        foreach ($group->getProducts() as $product) {
            $oldProductIdentifiers[] = $product->getIdentifier();
            // Remove products that are no longer in the group
            if (!in_array($product->getIdentifier(), $productIdentifiers)) {
                $group->removeProduct($product);
            }
        }

        // Extract the products that are not already in the group to add them to it
        $productIdentifiersToAdd = array_values(array_diff($productIdentifiers, $oldProductIdentifiers));

        if (empty($productIdentifiersToAdd)) {
            return;
        }

        $pqb = $this->productQueryBuilderFactory->create();
        $pqb->addFilter('identifier', Operators::IN_LIST, $productIdentifiersToAdd);

        $products = $pqb->execute();

        foreach ($products as $product) {
            $group->addProduct($product);
        }
    }
}
