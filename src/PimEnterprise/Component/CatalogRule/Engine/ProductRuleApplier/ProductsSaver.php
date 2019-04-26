<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\CatalogRule\Engine\ProductRuleApplier;

use Akeneo\Bundle\RuleEngineBundle\Model\RuleInterface;
use Akeneo\Component\StorageUtils\Cache\EntityManagerClearerInterface;
use Akeneo\Component\StorageUtils\Saver\BulkSaverInterface;
use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Pim\Bundle\VersioningBundle\Manager\VersionContext;
use Pim\Bundle\VersioningBundle\Manager\VersionManager;
use Pim\Component\Catalog\Model\EntityWithValuesInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductModel;
use Pim\Component\Catalog\Model\ProductModelInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Saves products when apply a rule
 *
 * @author Nicolas Dupont <nicolas@akeneo.com>
 */
class ProductsSaver
{
    /** @var BulkSaverInterface */
    protected $productSaver;

    /** @var BulkSaverInterface */
    protected $productModelSaver;

    /** @var VersionManager */
    protected $versionManager;

    /** @var TranslatorInterface */
    protected $translator;

    /** @var VersionContext */
    protected $versionContext;

    /** @var SaverInterface|null */
    private $productModelDescendantsSaver;

    /** @var EntityManagerClearerInterface|null */
    private $cacheClearer;

    /**
     * @param BulkSaverInterface                 $productSaver
     * @param BulkSaverInterface                 $productModelSaver
     * @param VersionManager                     $versionManager
     * @param VersionContext                     $versionContext
     * @param TranslatorInterface                $translator
     * @param SaverInterface|null                $productModelDescendantsSaver
     * @param EntityManagerClearerInterface|null $cacheClearer
     */
    public function __construct(
        BulkSaverInterface $productSaver,
        BulkSaverInterface $productModelSaver,
        VersionManager $versionManager,
        VersionContext $versionContext,
        TranslatorInterface $translator,
        SaverInterface $productModelDescendantsSaver = null, // TODO: @merge - remove '=null'
        EntityManagerClearerInterface $cacheClearer = null // TODO: @merge - remove '=null'
    ) {
        $this->productSaver = $productSaver;
        $this->productModelSaver = $productModelSaver;
        $this->versionManager = $versionManager;
        $this->translator = $translator;
        $this->versionContext = $versionContext;
        $this->productModelDescendantsSaver = $productModelDescendantsSaver;
        $this->cacheClearer = $cacheClearer;
    }

    /**
     * @param RuleInterface               $rule
     * @param EntityWithValuesInterface[] $entityWithValues
     */
    public function save(RuleInterface $rule, array $entityWithValues): void
    {
        $savingContext = $this->translator->trans(
            'pimee_catalog_rule.product.history',
            ['%rule%' => $rule->getCode()],
            null,
            'en'
        );
        $versioningState = $this->versionManager->isRealTimeVersioning();
        $this->versionContext->addContextInfo($savingContext, 'default');
        $this->versionManager->setRealTimeVersioning(true);

        $products = array_filter($entityWithValues, function ($item) {
            return $item instanceof ProductInterface;
        });
        $productModels = array_filter($entityWithValues, function ($item) {
            return $item instanceof ProductModelInterface;
        });

        $this->productSaver->saveAll($products);
        $this->productModelSaver->saveAll($productModels);
        $this->computeProductModelDescendants($productModels);

        $this->versionManager->setRealTimeVersioning($versioningState);
        $this->versionContext->unsetContextInfo('default');
    }

    /**
     * Explicitly updates the product models descendants of a product.
     *
     * @param ProductModel[] $productModels
     */
    private function computeProductModelDescendants(array $productModels): void
    {
        foreach ($productModels as $productModel) {
            if (null !== $this->productModelDescendantsSaver && null !== $this->cacheClearer) {
                $this->productModelDescendantsSaver->save($productModel);
                $this->cacheClearer->clear();
            }
        }
    }
}
