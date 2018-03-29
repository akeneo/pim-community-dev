<?php
declare(strict_types=1);

namespace spec\Pim\Bundle\CatalogBundle\Command;

use Akeneo\Component\StorageUtils\Cursor\CursorInterface;
use Akeneo\Component\StorageUtils\Indexer\BulkIndexerInterface;
use Akeneo\Component\StorageUtils\Saver\BulkSaverInterface;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\FamilyInterface;
use Pim\Component\Catalog\Model\FamilyVariantInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ValueCollectionInterface;
use Pim\Component\Catalog\Model\ValueInterface;
use Pim\Component\Catalog\Model\VariantProductInterface;
use Pim\Component\Catalog\Query\Filter\Operators;
use Pim\Component\Catalog\Query\ProductQueryBuilderFactoryInterface;
use Pim\Component\Catalog\Query\ProductQueryBuilderInterface;
use Prophecy\Argument;
use Prophecy\Promise\ReturnPromise;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RemoveWrongBooleanValuesOnVariantProductsCommandSpec extends ObjectBehavior
{
    function it_has_a_name()
    {
        $this->getName()->shouldReturn('pim:catalog:remove-wrong-values-on-variant-products');
    }

    function it_is_a_command()
    {
        $this->shouldBeAnInstanceOf(ContainerAwareCommand::class);
    }

    function it_removes_wrong_boolean_values_on_impacted_variant_products(
        ContainerInterface $container,
        InputInterface $input,
        OutputInterface $output,
        ProductQueryBuilderFactoryInterface $pqbFactory,
        ProductQueryBuilderInterface $pqb,
        CursorInterface $productsCursor,
        VariantProductInterface $variantProductImpacted,
        FamilyInterface $boots,
        AttributeInterface $booleanAttribute,
        FamilyVariantInterface $familyVariant,
        ValueCollectionInterface $valuesForVariation,
        ValueCollectionInterface $values,
        ValueInterface $booleanValue,
        ValidatorInterface $validator,
        ConstraintViolationListInterface $violationList,
        BulkSaverInterface $saver,
        BulkIndexerInterface $indexer
    ) {
        $container->get('pim_catalog.query.product_and_product_model_query_builder_factory')->willReturn($pqbFactory);
        $pqbFactory->create()->willReturn($pqb);

        $pqb->addFilter('parent', Operators::IS_NOT_EMPTY, null)->shouldBeCalled();
        $pqb->execute()->willReturn($productsCursor);

        $productsCount = count($variantProductImpacted);
        $productsCursor->valid()->will(
            function () use (&$productsCount) {
                return $productsCount-- > 0;
            }
        );
        $productsCursor->current()->will(new ReturnPromise([$variantProductImpacted]));
        $productsCursor->rewind()->shouldBeCalled();
        $productsCursor->next()->shouldBeCalled();

        $this->setContainer($container);

        $variantProductImpacted->getFamily()->willReturn($boots);
        $boots->getAttributes()->willReturn([$booleanAttribute]);
        $booleanAttribute->getType()->willReturn('pim_catalog_boolean');
        $booleanAttribute->getCode()->willReturn('bool_attribute');

        $variantProductImpacted->getFamilyVariant()->willReturn($familyVariant);
        $familyVariant->getLevelForAttributeCode('bool_attribute')->willReturn(0);
        $familyVariant->getNumberOfLevel()->willReturn(1);

        $variantProductImpacted->getValuesForVariation()->willReturn($valuesForVariation);
        $valuesForVariation->getByCodes('bool_attribute')->willReturn($booleanValue);

        $variantProductImpacted->getValues()->willReturn($values);
        $values->removeByAttribute($booleanAttribute)->shouldBeCalled();
        $variantProductImpacted->setValues($values)->shouldBeCalled();

        $container->get('pim_catalog.validator.product')->willReturn($validator);
        $validator->validate($variantProductImpacted)->willReturn($violationList);
        $violationList->count()->willReturn(0);

        $container->get('pim_catalog.saver.product')->willReturn($saver);
        $saver->saveAll([$variantProductImpacted])->shouldBeCalled();
        $container->get('pim_catalog.elasticsearch.indexer.product')->willReturn($indexer);
        $indexer->indexAll([$variantProductImpacted])->shouldBeCalled();

        $input->bind(Argument::any())->shouldBeCalled();
        $input->isInteractive()->shouldBeCalled();
        $input->hasArgument(Argument::any())->shouldBeCalled();
        $input->validate()->shouldBeCalled();

        $this->run($input, $output);
    }

    function it_does_not_update_product_without_boolean_in_their_family(
        ContainerInterface $container,
        InputInterface $input,
        OutputInterface $output,
        ProductQueryBuilderFactoryInterface $pqbFactory,
        ProductQueryBuilderInterface $pqb,
        CursorInterface $productsCursor,
        VariantProductInterface $variantProductNotImpacted,
        FamilyInterface $boots,
        AttributeInterface $textAttribute,
        FamilyVariantInterface $familyVariant
    ) {
        $container->get('pim_catalog.query.product_and_product_model_query_builder_factory')->willReturn($pqbFactory);
        $pqbFactory->create()->willReturn($pqb);

        $pqb->addFilter('parent', Operators::IS_NOT_EMPTY, null)->shouldBeCalled();
        $pqb->execute()->willReturn($productsCursor);

        $productsCount = count($variantProductNotImpacted);
        $productsCursor->valid()->will(
            function () use (&$productsCount) {
                return $productsCount-- > 0;
            }
        );
        $productsCursor->current()->will(new ReturnPromise([$variantProductNotImpacted]));
        $productsCursor->rewind()->shouldBeCalled();
        $productsCursor->next()->shouldBeCalled();

        $this->setContainer($container);

        $variantProductNotImpacted->getFamily()->willReturn($boots);
        $boots->getAttributes()->willReturn([$textAttribute]);
        $textAttribute->getType()->willReturn('pim_catalog_text');
        $textAttribute->getCode()->willReturn('text_attribute');

        $variantProductNotImpacted->getFamilyVariant()->willReturn($familyVariant);
        $familyVariant->getLevelForAttributeCode('bool_attribute')->willReturn(0);
        $familyVariant->getNumberOfLevel()->willReturn(1);

        $container->get('pim_catalog.saver.product')->shouldNotBeCalled();
        $container->get('pim_catalog.elasticsearch.indexer.product')->shouldNotBeCalled();

        $input->bind(Argument::any())->shouldBeCalled();
        $input->isInteractive()->shouldBeCalled();
        $input->hasArgument(Argument::any())->shouldBeCalled();
        $input->validate()->shouldBeCalled();

        $this->run($input, $output);
    }

    function it_does_not_update_product_if_boolean_is_on_product_level(
        ContainerInterface $container,
        InputInterface $input,
        OutputInterface $output,
        ProductQueryBuilderFactoryInterface $pqbFactory,
        ProductQueryBuilderInterface $pqb,
        CursorInterface $productsCursor,
        VariantProductInterface $variantProductNotImpacted,
        FamilyInterface $boots,
        AttributeInterface $booleanAttribute,
        FamilyVariantInterface $familyVariant,
        BulkSaverInterface $saver,
        BulkIndexerInterface $indexer
    ) {
        $container->get('pim_catalog.query.product_and_product_model_query_builder_factory')->willReturn($pqbFactory);
        $pqbFactory->create()->willReturn($pqb);

        $pqb->addFilter('parent', Operators::IS_NOT_EMPTY, null)->shouldBeCalled();
        $pqb->execute()->willReturn($productsCursor);

        $productsCount = count($variantProductNotImpacted);
        $productsCursor->valid()->will(
            function () use (&$productsCount) {
                return $productsCount-- > 0;
            }
        );
        $productsCursor->current()->will(new ReturnPromise([$variantProductNotImpacted]));
        $productsCursor->rewind()->shouldBeCalled();
        $productsCursor->next()->shouldBeCalled();

        $this->setContainer($container);

        $variantProductNotImpacted->getFamily()->willReturn($boots);
        $boots->getAttributes()->willReturn([$booleanAttribute]);
        $booleanAttribute->getType()->willReturn('pim_catalog_boolean');
        $booleanAttribute->getCode()->willReturn('bool_attribute');

        $variantProductNotImpacted->getFamilyVariant()->willReturn($familyVariant);
        $familyVariant->getLevelForAttributeCode('bool_attribute')->willReturn(1);
        $familyVariant->getNumberOfLevel()->willReturn(1);

        $container->get('pim_catalog.saver.product')->shouldNotBeCalled($saver);
        $container->get('pim_catalog.elasticsearch.indexer.product')->shouldNotBeCalled($indexer);

        $input->bind(Argument::any())->shouldBeCalled();
        $input->isInteractive()->shouldBeCalled();
        $input->hasArgument(Argument::any())->shouldBeCalled();
        $input->validate()->shouldBeCalled();

        $this->run($input, $output);
    }

    function it_does_not_update_product_if_product_does_not_have_any_value_on_this_attribute(
        ContainerInterface $container,
        InputInterface $input,
        OutputInterface $output,
        ProductQueryBuilderFactoryInterface $pqbFactory,
        ProductQueryBuilderInterface $pqb,
        CursorInterface $productsCursor,
        VariantProductInterface $variantProductNotImpacted,
        FamilyInterface $boots,
        AttributeInterface $booleanAttribute,
        FamilyVariantInterface $familyVariant,
        ValueCollectionInterface $valuesForVariation,
        BulkSaverInterface $saver,
        BulkIndexerInterface $indexer
    ) {
        $container->get('pim_catalog.query.product_and_product_model_query_builder_factory')->willReturn($pqbFactory);
        $pqbFactory->create()->willReturn($pqb);

        $pqb->addFilter('parent', Operators::IS_NOT_EMPTY, null)->shouldBeCalled();
        $pqb->execute()->willReturn($productsCursor);

        $productsCount = count($variantProductNotImpacted);
        $productsCursor->valid()->will(
            function () use (&$productsCount) {
                return $productsCount-- > 0;
            }
        );
        $productsCursor->current()->will(new ReturnPromise([$variantProductNotImpacted]));
        $productsCursor->rewind()->shouldBeCalled();
        $productsCursor->next()->shouldBeCalled();

        $this->setContainer($container);

        $variantProductNotImpacted->getFamily()->willReturn($boots);
        $boots->getAttributes()->willReturn([$booleanAttribute]);
        $booleanAttribute->getType()->willReturn('pim_catalog_boolean');
        $booleanAttribute->getCode()->willReturn('bool_attribute');

        $variantProductNotImpacted->getFamilyVariant()->willReturn($familyVariant);
        $familyVariant->getLevelForAttributeCode('bool_attribute')->willReturn(0);
        $familyVariant->getNumberOfLevel()->willReturn(1);

        $variantProductNotImpacted->getValuesForVariation()->willReturn($valuesForVariation);
        $valuesForVariation->getByCodes('bool_attribute')->willReturn(null);

        $container->get('pim_catalog.saver.product')->shouldNotBeCalled($saver);
        $container->get('pim_catalog.elasticsearch.indexer.product')->shouldNotBeCalled($indexer);

        $input->bind(Argument::any())->shouldBeCalled();
        $input->isInteractive()->shouldBeCalled();
        $input->hasArgument(Argument::any())->shouldBeCalled();
        $input->validate()->shouldBeCalled();

        $this->run($input, $output);
    }

    function it_does_not_update_product_if_product_is_not_a_variant_product(
        ContainerInterface $container,
        InputInterface $input,
        OutputInterface $output,
        ProductQueryBuilderFactoryInterface $pqbFactory,
        ProductQueryBuilderInterface $pqb,
        CursorInterface $productsCursor,
        ProductInterface $productNotImpacted,
        BulkSaverInterface $saver,
        BulkIndexerInterface $indexer
    ) {
        $container->get('pim_catalog.query.product_and_product_model_query_builder_factory')->willReturn($pqbFactory);
        $pqbFactory->create()->willReturn($pqb);

        $pqb->addFilter('parent', Operators::IS_NOT_EMPTY, null)->shouldBeCalled();
        $pqb->execute()->willReturn($productsCursor);

        $productsCount = count($productNotImpacted);
        $productsCursor->valid()->will(
            function () use (&$productsCount) {
                return $productsCount-- > 0;
            }
        );
        $productsCursor->current()->will(new ReturnPromise([$productNotImpacted]));
        $productsCursor->rewind()->shouldBeCalled();
        $productsCursor->next()->shouldBeCalled();

        $this->setContainer($container);

        $container->get('pim_catalog.saver.product')->shouldNotBeCalled($saver);
        $container->get('pim_catalog.elasticsearch.indexer.product')->shouldNotBeCalled($indexer);

        $input->bind(Argument::any())->shouldBeCalled();
        $input->isInteractive()->shouldBeCalled();
        $input->hasArgument(Argument::any())->shouldBeCalled();
        $input->validate()->shouldBeCalled();

        $this->run($input, $output);
    }

    function it_throws_an_exception_if_after_being_updated_the_product_is_not_valid(
        ContainerInterface $container,
        InputInterface $input,
        OutputInterface $output,
        ProductQueryBuilderFactoryInterface $pqbFactory,
        ProductQueryBuilderInterface $pqb,
        CursorInterface $productsCursor,
        VariantProductInterface $variantProductImpacted,
        FamilyInterface $boots,
        AttributeInterface $booleanAttribute,
        FamilyVariantInterface $familyVariant,
        ValueCollectionInterface $valuesForVariation,
        ValueCollectionInterface $values,
        ValueInterface $booleanValue,
        ValidatorInterface $validator,
        ConstraintViolationListInterface $violationList,
        BulkSaverInterface $saver,
        BulkIndexerInterface $indexer
    ) {
        $container->get('pim_catalog.query.product_and_product_model_query_builder_factory')->willReturn($pqbFactory);
        $pqbFactory->create()->willReturn($pqb);

        $pqb->addFilter('parent', Operators::IS_NOT_EMPTY, null)->shouldBeCalled();
        $pqb->execute()->willReturn($productsCursor);

        $productsCount = count($variantProductImpacted);
        $productsCursor->valid()->will(
            function () use (&$productsCount) {
                return $productsCount-- > 0;
            }
        );
        $productsCursor->current()->will(new ReturnPromise([$variantProductImpacted]));
        $productsCursor->rewind()->shouldBeCalled();

        $this->setContainer($container);

        $variantProductImpacted->getFamily()->willReturn($boots);
        $boots->getAttributes()->willReturn([$booleanAttribute]);
        $booleanAttribute->getType()->willReturn('pim_catalog_boolean');
        $booleanAttribute->getCode()->willReturn('bool_attribute');

        $variantProductImpacted->getFamilyVariant()->willReturn($familyVariant);
        $familyVariant->getLevelForAttributeCode('bool_attribute')->willReturn(0);
        $familyVariant->getNumberOfLevel()->willReturn(1);

        $variantProductImpacted->getValuesForVariation()->willReturn($valuesForVariation);
        $valuesForVariation->getByCodes('bool_attribute')->willReturn($booleanValue);

        $variantProductImpacted->getValues()->willReturn($values);
        $values->removeByAttribute($booleanAttribute)->shouldBeCalled();
        $variantProductImpacted->setValues($values)->shouldBeCalled();

        $container->get('pim_catalog.validator.product')->willReturn($validator);
        $validator->validate($variantProductImpacted)->willReturn($violationList);
        $violationList->count()->willReturn(1);

        $container->get('pim_catalog.saver.product')->shouldNotBeCalled($saver);
        $container->get('pim_catalog.elasticsearch.indexer.product')->shouldNotBeCalled($indexer);

        $input->bind(Argument::any())->shouldBeCalled();
        $input->isInteractive()->shouldBeCalled();
        $input->hasArgument(Argument::any())->shouldBeCalled();
        $input->validate()->shouldBeCalled();

        $variantProductImpacted->getIdentifier()->willReturn('TOTO');

        $this->shouldThrow(\LogicException::class)->during('run', [$input, $output]);
    }
}
