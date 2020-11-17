<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Updater\Setter;

use Akeneo\Pim\Enrichment\Component\Category\Model\CategoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Exception\UnknownCategoryException;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface;
use Akeneo\Tool\Component\Classification\CategoryAwareInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Sets the category field
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CategoryFieldSetter extends AbstractFieldSetter
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
    public function setFieldData($entity, $field, $data, array $options = [])
    {
        if (!$entity instanceof CategoryAwareInterface) {
            throw InvalidObjectException::objectExpected($entity, EntityWithValuesInterface::class);
        }

        $this->checkData($field, $data);

        $categories = new ArrayCollection();
        foreach ($data as $categoryCode) {
            $category = $this->getCategory($categoryCode);

            if (null === $category) {
                throw new UnknownCategoryException($field, $categoryCode, static::class);
            }
            $categories->add($category);
        }

        $formerCategories = $entity->getCategories();
        $categoriesToAdd = $categories->filter(
            function (CategoryInterface $category) use ($formerCategories) {
                return !$formerCategories->contains($category);
            }
        );
        foreach ($categoriesToAdd as $categoryToAdd) {
            $entity->addCategory($categoryToAdd);
        }

        $categoriesToRemove = $formerCategories->filter(
            function (Categoryinterface $category) use ($categories) {
                return !$categories->contains($category);
            }
        );
        foreach ($categoriesToRemove as $categoryToRemove) {
            $entity->removeCategory($categoryToRemove);
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

    /**
     * @param string $categoryCode
     *
     * @return CategoryInterface
     */
    protected function getCategory($categoryCode)
    {
        $category = $this->categoryRepository->findOneByIdentifier($categoryCode);

        return $category;
    }
}
