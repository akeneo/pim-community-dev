<?php

namespace spec\PimEnterprise\Bundle\CatalogRuleBundle\Engine\ProductRuleApplier;

use Akeneo\Bundle\RuleEngineBundle\Model\RuleInterface;
use Akeneo\Component\StorageUtils\Saver\BulkSaverInterface;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\VersioningBundle\Manager\VersionContext;
use Pim\Bundle\VersioningBundle\Manager\VersionManager;
use Prophecy\Argument;
use Symfony\Component\Translation\TranslatorInterface;

class ProductsSaverSpec extends ObjectBehavior
{
    function let(
        BulkSaverInterface $productSaver,
        VersionManager $versionManager,
        VersionContext $versionContext,
        TranslatorInterface $translator
    ) {
        $this->beConstructedWith(
            $productSaver,
            $versionManager,
            $versionContext,
            $translator
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('PimEnterprise\Bundle\CatalogRuleBundle\Engine\ProductRuleApplier\ProductsSaver');
    }

    function it_saves_products(
        $productSaver,
        $versionManager,
        $versionContext,
        $translator,
        ProductInterface $product,
        RuleInterface $rule
    ) {
        $translator->trans(Argument::cetera())->willReturn('Applied rule "rule_one"');
        $versionManager->isRealTimeVersioning()->willReturn(false);
        $versionContext->addContextInfo('Applied rule "rule_one"')->shouldBeCalled();
        $versionManager->setRealTimeVersioning(false)->shouldBeCalled();
        $productSaver->saveAll(Argument::any(), ['recalculate' => false, 'schedule' => true])->shouldBeCalled();

        $this->save($rule, [$product]);
    }
}
