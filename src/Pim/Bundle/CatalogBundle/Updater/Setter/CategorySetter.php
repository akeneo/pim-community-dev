<?php

namespace Pim\Bundle\CatalogBundle\Updater\Setter;

use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Repository\ReferableEntityRepositoryInterface;
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
     * @param ReferableEntityRepositoryInterface $categoryRepository
     * @param array                              $supportedTypes
     */
    public function __construct(
        ReferableEntityRepositoryInterface $categoryRepository,
        array $supportedFields
    ) {
        $this->categoryRepository = $categoryRepository;
        $this->supportedFields    = $supportedFields;
    }

    /**
     * {@inheritdoc}
     */
    public function setFieldData(ProductInterface $product, $field, $data, array $options = [])
    {
        $this->checkData($field, $data);

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
