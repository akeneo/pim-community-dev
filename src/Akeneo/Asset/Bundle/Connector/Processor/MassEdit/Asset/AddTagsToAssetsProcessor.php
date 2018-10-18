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
use Akeneo\Asset\Component\Model\TagInterface;
use Akeneo\Pim\Permission\Component\Attributes;
use Akeneo\Tool\Component\Batch\Item\DataInvalidItem;
use Akeneo\Tool\Component\Classification\Repository\TagRepositoryInterface;
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
     * @param TagRepositoryInterface        $repository
     * @param ValidatorInterface            $validator
     * @param AuthorizationCheckerInterface $authorizationChecker
     */
    public function __construct(
        TagRepositoryInterface $repository,
        ValidatorInterface $validator,
        AuthorizationCheckerInterface $authorizationChecker
    ) {
        $this->repository = $repository;
        $this->validator = $validator;
        $this->authorizationChecker = $authorizationChecker;
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
                if (null !== $tag) {
                    $asset->addTag($tag);
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
        $tag = $this->repository->findOneByIdentifier($code);

        if (null === $tag) {
            $this->stepExecution->addWarning(
                'pim_enrich.mass_edit_action.add-tags-to-assets.message.error',
                [],
                new DataInvalidItem([$code])
            );
        }

        return $tag;
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
