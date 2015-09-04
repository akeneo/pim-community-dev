<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\ProductAsset\Updater;

use Akeneo\Component\Classification\Repository\CategoryRepositoryInterface;
use Akeneo\Component\Classification\Repository\TagRepositoryInterface;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Doctrine\Common\Util\ClassUtils;
use PimEnterprise\Component\ProductAsset\Model\AssetInterface;
use PimEnterprise\Component\ProductAsset\Model\CategoryInterface;
use PimEnterprise\Component\ProductAsset\Model\TagInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;

/**
 * Updates and validates a asset
 *
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
class AssetUpdater implements ObjectUpdaterInterface
{
    const INNER_SEPARATOR = ',';

    /** @var TagRepositoryInterface */
    protected $tagRepository;

    /** @var CategoryRepositoryInterface */
    protected $categoryRepository;

    /** @var PropertyAccessor */
    protected $accessor;

    /**
     * @param TagRepositoryInterface      $tagRepository
     * @param CategoryRepositoryInterface $categoryRepository
     */
    public function __construct(TagRepositoryInterface $tagRepository, CategoryRepositoryInterface $categoryRepository)
    {
        $this->tagRepository      = $tagRepository;
        $this->categoryRepository = $categoryRepository;
        $this->accessor           = PropertyAccess::createPropertyAccessor();
    }

    /**
     * {@inheritdoc}
     */
    public function update($asset, array $data, array $options = [])
    {
        if (!$asset instanceof AssetInterface) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Expects a "PimEnterprise\Component\ProductAsset\Model\AssetInterface", "%s" provided.',
                    ClassUtils::getClass($asset)
                )
            );
        }

        foreach ($data as $field => $item) {
            $this->setData($asset, $field, $item);
        }

        return $this;
    }

    /**
     * @param AssetInterface $asset
     * @param string         $field
     * @param mixed          $data
     *
     * @throws \InvalidArgumentException
     */
    protected function setData(AssetInterface $asset, $field, $data)
    {
        switch ($field) {
            case 'tags':
                $this->setTags($asset, $data);
                break;
            case 'categories':
                $this->setCategories($asset, $data);
                break;
            case 'end_of_use':
                $this->validateDateFormat($data);
                $asset->setEndOfUseAt(new \DateTime($data));
                break;
            default:
                $this->accessor->setValue($asset, $field, $data);
        }
    }

    /**
     * It sets the tags by diff with existing tags and then remove other tags (due to doctrine UOW that does not
     * update link between the tags and the asset).
     *
     * @param AssetInterface $asset
     * @param array          $data
     */
    protected function setTags(AssetInterface $asset, array $data)
    {
        $tagCodes = [];
        $newTags  = $data;

        if ('' !== $asset->getTagCodes()) {
            $tagCodes = explode(static::INNER_SEPARATOR, $asset->getTagCodes());
        }

        if (!empty($tagCodes)) {
            $newTags = array_diff($data, $tagCodes);
        }

        foreach ($newTags as $tagCode) {
            $asset->addTag($this->getTagByCode($tagCode));
        }

        if (!empty($tagCodes)) {
            $toRemoveTags = array_diff($tagCodes, $data);
            $this->removeTags($asset, $toRemoveTags);
        }
    }

    /**
     * It sets the categories by diff with existing tags and then remove other categories (due to doctrine UOW that
     * does not update link between the categories and the asset).
     *
     * @param AssetInterface $asset
     * @param array          $data
     */
    protected function setCategories(AssetInterface $asset, array $data)
    {
        $categoriesCode = [];
        $newCategories  = $data;

        if ('' !== $asset->getCategoryCodes()) {
            $categoriesCode = explode(static::INNER_SEPARATOR, $asset->getCategoryCodes());
        }

        if (!empty($categoriesCode)) {
            $newCategories = array_diff($newCategories, $categoriesCode);
        }

        foreach ($newCategories as $categoryCode) {
            $asset->addCategory($this->getCategoryByCode($categoryCode));
        }

        if (!empty($categoriesCode)) {
            $categories = array_diff($categoriesCode, $data);
            $this->removeCategories($asset, $categories);
        }
    }

    /**
     * @param string $data
     *
     * @throws \InvalidArgumentException
     */
    protected function validateDateFormat($data)
    {
        $dateValues = explode('-', $data);

        if (count($dateValues) !== 3
            || (!is_numeric($dateValues[0]) || !is_numeric($dateValues[1]) || !is_numeric($dateValues[2]))
            || !checkdate($dateValues[1], $dateValues[2], $dateValues[0])
        ) {
            throw new \InvalidArgumentException(
                sprintf('Asset expects a string with the format "yyyy-mm-dd" as data, "%s" given', $data)
            );
        }
    }

    /**
     * @param string $tagCode
     *
     * @throws \InvalidArgumentException
     *
     * @return TagInterface
     */
    protected function getTagByCode($tagCode)
    {
        $tag = $this->tagRepository->findOneByIdentifier($tagCode);

        if (null === $tag) {
            throw new \InvalidArgumentException(sprintf('Tag with "%s" code does not exist', $tagCode));
        }

        return $tag;
    }

    /**
     * @param string $categoryCode
     *
     * @throws \InvalidArgumentException
     *
     * @return CategoryInterface
     */
    protected function getCategoryByCode($categoryCode)
    {
        $category = $this->categoryRepository->findOneByIdentifier($categoryCode);

        if (null === $category) {
            throw new \InvalidArgumentException(sprintf('Category with "%s" code does not exist', $categoryCode));
        }

        return $category;
    }

    /**
     * @param AssetInterface $asset
     * @param array          $tags
     */
    protected function removeTags(AssetInterface $asset, array $tags)
    {
        foreach ($tags as $tagCode) {
            $asset->removeTag($this->getTagByCode($tagCode));
        }
    }

    /**
     * @param AssetInterface $asset
     * @param array          $categories
     */
    protected function removeCategories(AssetInterface $asset, array $categories)
    {
        foreach ($categories as $categoryCode) {
            $asset->removeCategory($this->getCategoryByCode($categoryCode));
        }
    }
}
