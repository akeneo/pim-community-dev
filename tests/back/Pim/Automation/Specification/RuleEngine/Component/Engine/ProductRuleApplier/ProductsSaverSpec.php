<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\RuleEngine\Component\Engine\ProductRuleApplier;

use Akeneo\Tool\Bundle\RuleEngineBundle\Model\RuleInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Tool\Bundle\VersioningBundle\Manager\VersionContext;
use Akeneo\Tool\Bundle\VersioningBundle\Manager\VersionManager;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Prophecy\Argument;
use Symfony\Contracts\Translation\TranslatorInterface;

class ProductsSaverSpec extends ObjectBehavior
{
    function let(
        BulkSaverInterface $productSaver,
        BulkSaverInterface $productModelSaver,
        VersionManager $versionManager,
        VersionContext $versionContext,
        TranslatorInterface $translator
    ) {
        $this->beConstructedWith(
            $productSaver,
            $productModelSaver,
            $versionManager,
            $versionContext,
            $translator
        );
    }

    function it_saves_products_and_product_models(
        $productSaver,
        $productModelSaver,
        $versionManager,
        $versionContext,
        $translator,
        ProductInterface $productA,
        ProductInterface $productB,
        ProductModelInterface $productModelA,
        ProductModelInterface $productModelB,
        RuleInterface $rule
    ) {
        $translator->trans(Argument::cetera())->willReturn('Applied rule "rule_one"');

        $versionManager->isRealTimeVersioning()->willReturn(true);
        $versionContext->addContextInfo('Applied rule "rule_one"', 'default')->shouldBeCalled();

        $productSaver->saveAll([0 => $productA, 1 => $productB])->shouldBeCalled();
        $productModelSaver->saveAll([2 => $productModelA, 3 => $productModelB])->shouldBeCalled();

        $versionManager->setRealTimeVersioning(true)->shouldBeCalled();
        $versionContext->unsetContextInfo('default')->shouldBeCalled();

        $this->save($rule, [$productA, $productB, $productModelA, $productModelB]);
    }
}
