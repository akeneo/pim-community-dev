<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
*/

namespace Akeneo\Pim\Permission\Bundle\MassEdit\Processor;

use Akeneo\Pim\Enrichment\Component\Product\Connector\Processor\MassEdit\EditCommonAttributesProcessor as BaseProcessor;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Pim\Permission\Component\Attributes;
use Akeneo\Tool\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * It edits a product value but check if the user has right to mass edit the product (if he is the owner).
 *
 * @author Adrien Pétremann <adrien.petremann@akeneo.com>
 *
 * @deprecated please use Akeneo\Pim\Permission\Bundle\MassEdit\Processor\EditAttributesProcessor
 *             instead, will be removed in 2.1.
 */
class EditCommonAttributesProcessor extends BaseProcessor
{
    /** @var AuthorizationCheckerInterface */
    protected $authorizationChecker;

    /**
     * @param ValidatorInterface            $validator
     * @param ProductRepositoryInterface    $productRepository
     * @param ObjectUpdaterInterface        $productUpdater
     * @param ObjectDetacherInterface       $productDetacher
     * @param AuthorizationCheckerInterface $authorizationChecker
     */
    public function __construct(
        ValidatorInterface $validator,
        ProductRepositoryInterface $productRepository,
        ObjectUpdaterInterface $productUpdater,
        ObjectDetacherInterface $productDetacher,
        AuthorizationCheckerInterface $authorizationChecker
    ) {
        parent::__construct(
            $validator,
            $productRepository,
            $productUpdater,
            $productDetacher
        );

        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * {@inheritdoc}
     */
    protected function isProductEditable(ProductInterface $product)
    {
        if (!$this->authorizationChecker->isGranted(Attributes::OWN, $product)
            && !$this->authorizationChecker->isGranted(Attributes::EDIT, $product)
        ) {
            return false;
        }

        return parent::isProductEditable($product);
    }
}
