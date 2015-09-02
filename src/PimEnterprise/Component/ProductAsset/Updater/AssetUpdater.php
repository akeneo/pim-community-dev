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
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;

/**
 * Updates and validates a asset
 *
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
class AssetUpdater implements ObjectUpdaterInterface
{
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
     * @param AssetInterface $asset
     * @param mixed          $data
     */
    protected function setTags(AssetInterface $asset, $data)
    {
        $asset->getTags()->clear();
        foreach ($data as $tagCode) {
            if (null === $tag = $this->tagRepository->findOneByIdentifier($tagCode)) {
                throw new \InvalidArgumentException(sprintf('Tag with "%s" code does not exist', $tagCode));
            }

            $asset->addTag($tag);
        }
    }

    /**
     * @param AssetInterface $asset
     * @param mixed          $data
     */
    protected function setCategories(AssetInterface $asset, $data)
    {
        $asset->getCategories()->clear();
        foreach ($data as $categoryCode) {
            if (null === $category = $this->categoryRepository->findOneByIdentifier($categoryCode)) {
                throw new \InvalidArgumentException(sprintf('Category with "%s" code does not exist', $categoryCode));
            }

            $asset->addCategory($category);
        }
    }

    /**
     * @param string $data
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
}
