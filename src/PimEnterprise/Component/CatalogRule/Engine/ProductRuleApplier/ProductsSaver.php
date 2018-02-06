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
use Akeneo\Component\StorageUtils\Saver\BulkSaverInterface;
use Pim\Bundle\VersioningBundle\Manager\VersionContext;
use Pim\Bundle\VersioningBundle\Manager\VersionManager;
use Pim\Component\Catalog\Model\EntityWithValuesInterface;
use Pim\Component\Catalog\Model\ProductInterface;
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

    /**
     * @param BulkSaverInterface  $productSaver
     * @param BulkSaverInterface  $productModelSaver
     * @param VersionManager      $versionManager
     * @param VersionContext      $versionContext
     * @param TranslatorInterface $translator
     */
    public function __construct(
        BulkSaverInterface $productSaver,
        BulkSaverInterface $productModelSaver,
        VersionManager $versionManager,
        VersionContext $versionContext,
        TranslatorInterface $translator
    ) {
        $this->productSaver = $productSaver;
        $this->productModelSaver = $productModelSaver;
        $this->versionManager = $versionManager;
        $this->translator = $translator;
        $this->versionContext = $versionContext;
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

        $this->versionManager->setRealTimeVersioning($versioningState);
        $this->versionContext->unsetContextInfo('default');
    }
}
