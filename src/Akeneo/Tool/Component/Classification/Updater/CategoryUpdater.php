<?php

namespace Akeneo\Tool\Component\Classification\Updater;

use Akeneo\Tool\Component\Classification\Model\CategoryInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use Akeneo\Tool\Component\StorageUtils\Exception\UnknownPropertyException;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Doctrine\Common\Util\ClassUtils;
use Symfony\Component\PropertyAccess\Exception\NoSuchPropertyException;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;

/**
 * Updates a category.
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CategoryUpdater implements ObjectUpdaterInterface
{
    /** @var PropertyAccessor */
    protected $accessor;

    /** @var IdentifiableObjectRepositoryInterface */
    protected $categoryRepository;

    /**
     * @param IdentifiableObjectRepositoryInterface $categoryRepository
     */
    public function __construct(IdentifiableObjectRepositoryInterface $categoryRepository)
    {
        $this->accessor = PropertyAccess::createPropertyAccessor();
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function update($category, array $data, array $options = [])
    {
        if (!$category instanceof CategoryInterface) {
            throw InvalidObjectException::objectExpected(
                ClassUtils::getClass($category),
                CategoryInterface::class
            );
        }

        foreach ($data as $field => $value) {
            $this->validateDataType($field, $value);
            $this->setData($category, $field, $value);
        }

        return $this;
    }

    /**
     * Validate the data type of a field.
     *
     * @param string $field
     * @param mixed  $data
     *
     * @throws InvalidPropertyTypeException
     * @throws UnknownPropertyException
     */
    protected function validateDataType($field, $data)
    {
        if ('labels' === $field) {
            if (!is_array($data)) {
                throw InvalidPropertyTypeException::arrayExpected('labels', static::class, $data);
            }

            foreach ($data as $localeCode => $label) {
                if (null !== $label && !is_scalar($label)) {
                    throw InvalidPropertyTypeException::validArrayStructureExpected(
                        'labels',
                        'one of the labels is not a scalar',
                        static::class,
                        $data
                    );
                }
            }
        } elseif (in_array($field, ['code', 'parent'])) {
            if (null !== $data && !is_scalar($data)) {
                throw InvalidPropertyTypeException::scalarExpected($field, static::class, $data);
            }
        } else {
            throw UnknownPropertyException::unknownProperty($field);
        }
    }

    /**
     * @param CategoryInterface $category
     * @param string            $field
     * @param mixed             $data
     *
     * @throws InvalidPropertyException
     * @throws UnknownPropertyException
     */
    protected function setData(CategoryInterface $category, $field, $data)
    {
        if ('labels' === $field) {
            foreach ($data as $localeCode => $label) {
                $category->setLocale($localeCode);
            }
        } elseif ('parent' === $field) {
            $this->updateParent($category, $data);
        } else {
            try {
                $this->accessor->setValue($category, $field, $data);
            } catch (NoSuchPropertyException $e) {
                throw UnknownPropertyException::unknownProperty($field, $e);
            }
        }
    }

    /**
     * @param CategoryInterface $category
     * @param string            $data
     *
     * @throws InvalidPropertyException
     */
    protected function updateParent(CategoryInterface $category, $data)
    {
        if (null === $data || '' === $data) {
            $category->setParent(null);

            return;
        }

        $categoryParent = $this->categoryRepository->findOneByIdentifier($data);
        if (null === $categoryParent) {
            throw InvalidPropertyException::validEntityCodeExpected(
                'parent',
                'category code',
                'The category does not exist',
                static::class,
                $data
            );
        }

        $category->setParent($categoryParent);
    }
}
