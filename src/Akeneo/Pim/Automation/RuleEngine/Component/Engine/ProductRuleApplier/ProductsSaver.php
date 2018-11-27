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

namespace Akeneo\Pim\Automation\RuleEngine\Component\Engine\ProductRuleApplier;

use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Tool\Bundle\RuleEngineBundle\Model\RuleInterface;
use Akeneo\Tool\Bundle\VersioningBundle\Manager\VersionContext;
use Akeneo\Tool\Bundle\VersioningBundle\Manager\VersionManager;
use Akeneo\Tool\Component\StorageUtils\Cache\EntityManagerClearerInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
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
     * @param BulkSaverInterface            $productSaver
     * @param BulkSaverInterface            $productModelSaver
     * @param VersionManager                $versionManager
     * @param VersionContext                $versionContext
     * @param TranslatorInterface           $translator
     * @param SaverInterface                $productModelDescendantsSaver
     * @param EntityManagerClearerInterface $cacheClearer
     */
    public function __construct(
        BulkSaverInterface $productSaver,
        BulkSaverInterface $productModelSaver,
        VersionManager $versionManager,
        VersionContext $versionContext,
        TranslatorInterface $translator,
        SaverInterface $productModelDescendantsSaver,
        EntityManagerClearerInterface $cacheClearer
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
        $this->versionManager->setRealTimeVersioning(false);

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
     * @param ProductModelInterface[] $productModels
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
