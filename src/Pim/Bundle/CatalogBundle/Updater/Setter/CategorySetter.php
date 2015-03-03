<?php

namespace Pim\Bundle\CatalogBundle\Updater\Setter;

use Akeneo\Bundle\StorageUtilsBundle\Repository\IdentifiableObjectRepositoryInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Updater\Setter\FieldSetterInterface;

/**
 * Sets the category field
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CategorySetter extends AbstractFieldSetter
{
    /**
     * @param IdentifiableObjectRepositoryInterface $categoryRepository
     * @param array                                 $supportedTypes
     */
    public function __construct(
        IdentifiableObjectRepositoryInterface $categoryRepository,
        array $supportedFields
    ) {
        $this->categoryRepository = $categoryRepository;
        $this->supportedFields    = $supportedFields;
    }

    /**
     * {@inheritdoc}
     *
     * Expected data input format : ["category_code"]
     */
    public function setFieldData(ProductInterface $product, $field, $data, array $options = [])
    {
        $this->checkData($field, $data);

        $categories = $product->getCategories();
        foreach ($categories as $category) {
            $product->removeCategory($category);
        }

        foreach ($data as $categoryCode) {
            $category = $this->categoryRepository->findOneByIdentifier($categoryCode);

            if (null === $category) {
                throw InvalidArgumentException::expected(
                    $field,
                    'existing category code',
                    'setter',
                    'category',
                    $categoryCode
                );
            }

            $product->addCategory($category);
        }
    }

    /**
     * Check if data are valid
     *
     * @param string $field
     * @param mixed  $data
     */
    protected function checkData($field, $data)
    {
        if (!is_array($data)) {
            throw InvalidArgumentException::arrayExpected(
                $field,
                'setter',
                'category',
                gettype($data)
            );
        }

        foreach ($data as $key => $value) {
            if (!is_string($value)) {
                throw InvalidArgumentException::arrayStringKeyExpected(
                    $field,
                    $key,
                    'setter',
                    'category',
                    gettype($value)
                );
            }
        }
    }
}
