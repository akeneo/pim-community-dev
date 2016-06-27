<?php

namespace Pim\Bundle\EnrichBundle\MassEditAction\Operation;

use Akeneo\Component\Classification\Model\CategoryInterface;

/**
 * Batch operation to classify entities
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Classify extends AbstractMassEditOperation
{
    /** @var CategoryInterface[] */
    protected $categories;

    /** @var string */
    protected $formType;

    /**
     * @param string $batchJobCode
     * @param string $itemsName
     * @param string $formType
     */
    public function __construct($batchJobCode, $itemsName, $formType)
    {
        $this->batchJobCode = $batchJobCode;
        $this->itemsName = $itemsName;
        $this->formType = $formType;
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
        return $this->formType;
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
