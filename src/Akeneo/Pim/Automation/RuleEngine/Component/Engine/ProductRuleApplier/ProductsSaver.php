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
use Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Saves products when apply a rule
 *
 * @author Nicolas Dupont <nicolas@akeneo.com>
 */
class ProductsSaver
{
    protected BulkSaverInterface $productSaver;
    protected BulkSaverInterface $productModelSaver;
    protected VersionManager $versionManager;
    protected TranslatorInterface $translator;
    protected VersionContext $versionContext;

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
        $this->versionManager->setRealTimeVersioning(true);

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
