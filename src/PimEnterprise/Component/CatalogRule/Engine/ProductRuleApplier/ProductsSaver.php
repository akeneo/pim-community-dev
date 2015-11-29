<?php

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
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Bundle\VersioningBundle\Manager\VersionContext;
use Pim\Bundle\VersioningBundle\Manager\VersionManager;
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

    /** @var VersionManager */
    protected $versionManager;

    /** @var TranslatorInterface */
    protected $translator;

    /** @var VersionContext */
    protected $versionContext;

    /**
     * @param BulkSaverInterface  $productSaver
     * @param VersionManager      $versionManager
     * @param VersionContext      $versionContext
     * @param TranslatorInterface $translator
     */
    public function __construct(
        BulkSaverInterface $productSaver,
        VersionManager $versionManager,
        VersionContext $versionContext,
        TranslatorInterface $translator
    ) {
        $this->productSaver   = $productSaver;
        $this->versionManager = $versionManager;
        $this->translator     = $translator;
        $this->versionContext = $versionContext;
    }

    /**
     * @param RuleInterface      $rule
     * @param ProductInterface[] $products
     */
    public function save(RuleInterface $rule, array $products)
    {
        $savingContext = $this->translator->trans(
            'pimee_catalog_rule.product.history',
            ['%rule%' => $rule->getCode()],
            null,
            'en'
        );
        $versioningState = $this->versionManager->isRealTimeVersioning();
        $this->versionContext->addContextInfo($savingContext);
        $this->versionManager->setRealTimeVersioning(false);
        $this->productSaver->saveAll($products, ['recalculate' => false, 'schedule' => true]);
        $this->versionManager->setRealTimeVersioning($versioningState);
    }
}
