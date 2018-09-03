<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Updater\Adder;

use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;

/**
 * Adds the category field
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CategoryFieldAdder extends AbstractFieldAdder
{
    /** @var IdentifiableObjectRepositoryInterface */
    protected $categoryRepository;

    /**
     * @param IdentifiableObjectRepositoryInterface $categoryRepository
     * @param array                                 $supportedFields
     */
    public function __construct(
        IdentifiableObjectRepositoryInterface $categoryRepository,
        array $supportedFields
    ) {
        $this->categoryRepository = $categoryRepository;
        $this->supportedFields = $supportedFields;
    }

    /**
     * {@inheritdoc}
     *
     * Expected data input format : ["category_code"]
     */
    public function addFieldData($product, $field, $data, array $options = [])
    {
        $this->checkData($field, $data);

        $categories = [];
        foreach ($data as $categoryCode) {
            $category = $this->categoryRepository->findOneByIdentifier($categoryCode);

            if (null !== $category) {
                $categories[] = $category;
            } else {
                throw InvalidPropertyException::validEntityCodeExpected(
                    $field,
                    'category code',
                    'The category does not exist',
                    static::class,
                    $categoryCode
                );
            }
        }

        foreach ($categories as $category) {
            $product->addCategory($category);
        }
    }

    /**
     * Check if data are valid
     *
     * @param string $field
     * @param mixed  $data
     *
     * @throws InvalidPropertyTypeException
     */
    protected function checkData($field, $data)
    {
        if (!is_array($data)) {
            throw InvalidPropertyTypeException::arrayExpected(
                $field,
                static::class,
                $data
            );
        }

        foreach ($data as $key => $value) {
            if (!is_string($value)) {
                throw InvalidPropertyTypeException::validArrayStructureExpected(
                    $field,
                    sprintf('one of the category codes is not a string, "%s" given', gettype($value)),
                    static::class,
                    $data
                );
            }
        }
    }
}
