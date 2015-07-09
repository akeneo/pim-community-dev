<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\ProductAsset\Processor\Denormalization;

use Akeneo\Bundle\BatchBundle\Item\InvalidItemException;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Pim\Component\Connector\ArrayConverter\StandardArrayConverterInterface;
use Pim\Component\Connector\Processor\Denormalization\AbstractProcessor;
use PimEnterprise\Bundle\ProductAssetBundle\Factory\TagFactory;
use PimEnterprise\Component\ProductAsset\Model\TagInterface;
use Symfony\Component\Validator\ValidatorInterface;

/**
 * Tag import processor, allows to,
 *  - create / update tag
 *  - return the valid tag, throw exceptions to skip invalid ones
 *
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
class TagProcessor extends AbstractProcessor
{
    /** @var StandardArrayConverterInterface */
    protected $tagConverter;

    /** @var ObjectUpdaterInterface */
    protected $tagUpdater;

    /** @var ValidatorInterface */
    protected $validator;

    /** @var TagFactory */
    protected $tagFactory;

    /**
     * @param StandardArrayConverterInterface       $tagConverter
     * @param IdentifiableObjectRepositoryInterface $repository
     * @param ObjectUpdaterInterface                $tagUpdater
     * @param ValidatorInterface                    $validator
     * @param TagFactory                            $tagFactory
     */
    public function __construct(
        StandardArrayConverterInterface $tagConverter,
        IdentifiableObjectRepositoryInterface $repository,
        ObjectUpdaterInterface $tagUpdater,
        TagFactory $tagFactory,
        ValidatorInterface $validator
    ) {
        parent::__construct($repository);

        $this->tagConverter = $tagConverter;
        $this->tagUpdater   = $tagUpdater;
        $this->tagFactory   = $tagFactory;
        $this->validator    = $validator;
    }

    /**
     * {@inheritdoc}
     *
     * Here the returned value is an array of tags.
     * We use a custom Step in order to have only one job for the assets import
     */
    public function process($item)
    {
        $convertedItem = $this->convertItemData($item);

        $tags = null;
        foreach ($convertedItem['tags'] as $tag) {
            $tagObject = $this->createTag($tag);

            if (null !== $tagObject) {
                try {
                    $this->updateTag($tagObject, ['code' => $tag]);
                } catch (\InvalidArgumentException $exception) {
                    $this->skipItemWithMessage($item, $exception->getMessage(), $exception);
                }

                $violations = $this->validateTag($tagObject);
                if ($violations->count() > 0) {
                    $this->skipItemWithConstraintViolations($item, $violations);
                }

                $tags[] = $tagObject;
            }
        }

        return $tags;
    }

    /**
     * @param array $item
     *
     * @return array
     */
    protected function convertItemData(array $item)
    {
        return $this->tagConverter->convert($item);
    }

    /**
     * Find or create the tag
     *
     * @param string $tag
     *
     * @return TagInterface
     */
    protected function createTag($tag)
    {
        $tag = $this->findObject($this->repository, ['code' => $tag]);

        return null === $tag ? $this->tagFactory->createTag() : null;
    }

    /**
     * Update the tag fields
     *
     * @param TagInterface $tag
     * @param array        $convertedItem
     */
    protected function updateTag(TagInterface $tag, array $convertedItem)
    {
        $this->tagUpdater->update($tag, $convertedItem);
    }

    /**
     * @param TagInterface $tag
     *
     * @throws InvalidItemException
     *
     * @return \Symfony\Component\Validator\ConstraintViolationListInterface
     */
    protected function validateTag(TagInterface $tag)
    {
        $violations = $this->validator->validate($tag);

        return $violations;
    }
}
