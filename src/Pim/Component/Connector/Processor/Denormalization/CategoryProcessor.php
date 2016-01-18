<?php

namespace Pim\Component\Connector\Processor\Denormalization;

use Akeneo\Bundle\BatchBundle\Item\InvalidItemException;
use Akeneo\Component\Classification\Factory\CategoryFactory;
use Akeneo\Component\Classification\Model\CategoryInterface;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Pim\Component\Connector\ArrayConverter\StandardArrayConverterInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Category import processor, allows to,
 *  - create / update category
 *  - return the valid category, throw exceptions to skip invalid ones
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CategoryProcessor extends AbstractProcessor
{
    /** @var StandardArrayConverterInterface */
    protected $categoryConverter;

    /** @var ObjectUpdaterInterface */
    protected $categoryUpdater;

    /** @var ValidatorInterface */
    protected $validator;

    /** @var CategoryFactory */
    protected $categoryFactory;

    /**
     * @param StandardArrayConverterInterface       $categoryConverter
     * @param IdentifiableObjectRepositoryInterface $repository
     * @param ObjectUpdaterInterface                $categoryUpdater
     * @param ValidatorInterface                    $validator
     * @param CategoryFactory                       $categoryFactory
     */
    public function __construct(
        StandardArrayConverterInterface $categoryConverter,
        IdentifiableObjectRepositoryInterface $repository,
        ObjectUpdaterInterface $categoryUpdater,
        CategoryFactory $categoryFactory,
        ValidatorInterface $validator
    ) {
        parent::__construct($repository);

        $this->categoryConverter = $categoryConverter;
        $this->categoryUpdater   = $categoryUpdater;
        $this->categoryFactory   = $categoryFactory;
        $this->validator         = $validator;
    }

    /**
     * {@inheritdoc}
     */
    public function process($item)
    {
        $convertedItem = $this->convertItemData($item);

        $category = $this->findOrCreateCategory($convertedItem);

        try {
            $this->updateCategory($category, $convertedItem);
        } catch (\InvalidArgumentException $exception) {
            $this->skipItemWithMessage($item, $exception->getMessage(), $exception);
        }

        $violations = $this->validateCategory($category);
        if ($violations->count() > 0) {
            $this->skipItemWithConstraintViolations($item, $violations);
        }

        return $category;
    }

    /**
     * @param array $item
     *
     * @return array
     */
    protected function convertItemData(array $item)
    {
        return $this->categoryConverter->convert($item);
    }

    /**
     * Find or create the category
     *
     * @param array $convertedItem
     *
     * @return CategoryInterface
     */
    protected function findOrCreateCategory(array $convertedItem)
    {
        $category = $this->findObject($this->repository, $convertedItem);
        if (null === $category) {
            $category = $this->categoryFactory->create();
        }

        return $category;
    }

    /**
     * Update the category fields
     *
     * @param CategoryInterface $category
     * @param array             $convertedItem
     */
    protected function updateCategory(CategoryInterface $category, array $convertedItem)
    {
        $this->categoryUpdater->update($category, $convertedItem);
    }

    /**
     * @param CategoryInterface $category
     *
     * @throws InvalidItemException
     *
     * @return \Symfony\Component\Validator\ConstraintViolationListInterface
     */
    protected function validateCategory(CategoryInterface $category)
    {
        $violations = $this->validator->validate($category);

        return $violations;
    }
}
