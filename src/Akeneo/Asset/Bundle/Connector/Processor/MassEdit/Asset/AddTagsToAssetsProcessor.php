<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Asset\Bundle\Connector\Processor\MassEdit\Asset;

use Akeneo\Asset\Component\Model\AssetInterface;
use Akeneo\Asset\Component\Model\Tag;
use Akeneo\Asset\Component\Model\TagInterface;
use Akeneo\Pim\Permission\Component\Attributes;
use Akeneo\Tool\Component\Batch\Item\DataInvalidItem;
use Akeneo\Tool\Component\Classification\Repository\TagRepositoryInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Processor to massively add tags on assets.
 *
 * @author Damien Carcel <damien.carcel@gmail.com>
 */
class AddTagsToAssetsProcessor extends AbstractProcessor
{
    /** @var TagRepositoryInterface */
    protected $repository;

    /** @var ValidatorInterface */
    protected $validator;

    /** @var AuthorizationCheckerInterface */
    private $authorizationChecker;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /** @var array Cache for new tags */
    private $newTags;

    /**
     * @todo merge master: remove the null default value for $objectManager
     */
    public function __construct(
        TagRepositoryInterface $repository,
        ValidatorInterface $validator,
        AuthorizationCheckerInterface $authorizationChecker,
        ObjectManager $objectManager = null
    ) {
        $this->repository = $repository;
        $this->validator = $validator;
        $this->authorizationChecker = $authorizationChecker;
        $this->objectManager = $objectManager;

        $this->newTags = [];
    }

    /**
     * {@inheritdoc}
     */
    public function process($asset)
    {
        if (!$this->authorizationChecker->isGranted(Attributes::EDIT, $asset)) {
            $this->stepExecution->addWarning(
                'pimee_product_asset.not_editable',
                ['%code%' => $asset->getCode()],
                new DataInvalidItem($asset)
            );
            $this->stepExecution->incrementSummaryInfo('skipped_assets');

            return null;
        }

        $actions = $this->getConfiguredActions();

        foreach ($actions as $action) {
            $this->addTagsToAsset($asset, $action);
        }

        if (false === $this->isAssetValid($asset)) {
            $this->stepExecution->incrementSummaryInfo('skipped_assets');

            return null;
        }

        return $asset;
    }

    /**
     * @param AssetInterface $asset
     * @param array          $action
     */
    protected function addTagsToAsset(AssetInterface $asset, array $action)
    {
        if ('tags' === $action['field']) {
            $value = $action['value'];
            foreach ($value as $tagCode) {
                $tag = $this->getTag($tagCode);
                /**
                 * @todo merge master: create the tag only if $tag === null and move the addTag() call just after the if, like that :
                 * if ($tag === null) {
                 *     $tag = new Tag();
                 *     $tag->setCode($tagCode);
                 *     $this->objectManager->persist($tag);
                 *     $this->newTags[$tagCode] = $tag;
                 * }
                 * $asset->addTag($tag);
                 */
                if ($tag !== null) {
                    $asset->addTag($tag);
                } elseif ($this->objectManager !== null) {
                    $tag = new Tag();
                    $tag->setCode($tagCode);
                    $this->objectManager->persist($tag);
                    $asset->addTag($tag);
                    $this->newTags[$tagCode] = $tag;
                }
            }
        }
    }

    /**
     * @param string $code
     *
     * @return null|TagInterface
     */
    protected function getTag($code)
    {
        if (array_key_exists($code, $this->newTags)) {
            return $this->newTags[$code];
        }

        return $this->repository->findOneByIdentifier($code);
    }

    /**
     * Validates the asset.
     *
     * @param AssetInterface $asset
     *
     * @return bool
     */
    protected function isAssetValid(AssetInterface $asset)
    {
        $violations = $this->validator->validate($asset);
        $this->addWarningMessage($violations, $asset);

        return 0 === $violations->count();
    }
}
