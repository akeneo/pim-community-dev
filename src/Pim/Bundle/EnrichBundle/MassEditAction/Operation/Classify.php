<?php

namespace Pim\Bundle\EnrichBundle\MassEditAction\Operation;

use Pim\Bundle\CatalogBundle\Model\CategoryInterface;

/**
 * Batch operation to classify products
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Classify extends AbstractMassEditOperation
{
    /** @var CategoryInterface[] */
    protected $categories;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->categories = [];
    }

    /**
     * @return CategoryInterface[]
     */
    public function getCategories()
    {
        return $this->categories;
    }

    /**
     * @param CategoryInterface[] $categories
     *
     * @return Classify
     */
    public function setCategories($categories)
    {
        $this->categories = $categories;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getOperationAlias()
    {
        return 'classify';
    }

    /**
     * {@inheritdoc}
     */
    public function getFormType()
    {
        return 'pim_enrich_mass_classify';
    }

    /**
     * {@inheritdoc}
     */
    public function getFormOptions()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getItemsName()
    {
        return 'product';
    }

    /**
     * {@inheritdoc}
     */
    public function getActions()
    {
        $categories = $this->getCategories();

        return [
            [
                'field' => 'categories',
                'value' => $this->getCategoriesCode($categories)
            ]
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getBatchJobCode()
    {
        return 'add_product_value';
    }

    /**
     * @param array $categories
     *
     * @return array
     */
    protected function getCategoriesCode(array $categories)
    {
        return array_map(
            function (CategoryInterface $category) {
                return $category->getCode();
            },
            $categories
        );
    }
}
