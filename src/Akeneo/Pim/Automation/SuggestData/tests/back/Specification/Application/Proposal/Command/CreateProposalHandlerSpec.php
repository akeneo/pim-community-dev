<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\SuggestData\Application\Proposal\Command;

use Akeneo\Pim\Automation\SuggestData\Application\Proposal\Command\CreateProposalCommand;
use Akeneo\Pim\Automation\SuggestData\Application\Proposal\Command\CreateProposalHandler;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\ProductSubscriptionInterface;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\SuggestedData;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Normalizer\Standard\SuggestedDataNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Builder\EntityWithValuesDraftBuilderInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Event\EntityWithValuesDraftEvents;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\EntityWithValuesDraftInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
class CreateProposalHandlerSpec extends ObjectBehavior
{
    function let(
        SuggestedDataNormalizer $suggestedDataNormalizer,
        ObjectUpdaterInterface $productUpdater,
        EntityWithValuesDraftBuilderInterface $draftBuilder,
        SaverInterface $draftSaver,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->beConstructedWith(
            $suggestedDataNormalizer,
            $productUpdater,
            $draftBuilder,
            $draftSaver,
            $eventDispatcher
        );
    }

    function it_is_a_create_proposal_handler()
    {
        $this->shouldHaveType(CreateProposalHandler::class);
    }

    function it_does_not_do_anything_if_product_is_variant(
        $suggestedDataNormalizer,
        CreateProposalCommand $command,
        ProductInterface $product,
        ProductSubscriptionInterface $subscription
    ) {
        $product->isVariant()->willReturn(true);
        $subscription->getProduct()->willReturn($product);
        $command->getProductSubscription()->willReturn($subscription);

        $subscription->getSuggestedData()->shouldNotBeCalled();
        $suggestedDataNormalizer->normalize(Argument::any())->shouldNotBeCalled();

        $this->handle($command)->shouldReturn(null);
    }

    function it_does_not_do_anything_if_product_is_not_categorized(
        $suggestedDataNormalizer,
        CreateProposalCommand $command,
        ProductInterface $product,
        ProductSubscriptionInterface $subscription
    ) {
        $product->isVariant()->willReturn(false);
        $product->getCategoryCodes()->willReturn([]);
        $subscription->getProduct()->willReturn($product);
        $command->getProductSubscription()->willReturn($subscription);

        $subscription->getSuggestedData()->shouldNotBeCalled();
        $suggestedDataNormalizer->normalize(Argument::any())->shouldNotBeCalled();

        $this->handle($command)->shouldReturn(null);
    }

    function it_does_not_do_anything_if_product_has_no_family(
        $suggestedDataNormalizer,
        CreateProposalCommand $command,
        ProductInterface $product,
        ProductSubscriptionInterface $subscription
    ) {
        $product->isVariant()->willReturn(false);
        $product->getCategoryCodes()->willReturn(['category_1']);
        $product->getFamily()->willReturn(null);
        $subscription->getProduct()->willReturn($product);
        $command->getProductSubscription()->willReturn($subscription);

        $subscription->getSuggestedData()->shouldNotBeCalled();
        $suggestedDataNormalizer->normalize(Argument::any())->shouldNotBeCalled();

        $this->handle($command)->shouldReturn(null);
    }

    function it_handles_a_create_proposal_command(
        $suggestedDataNormalizer,
        $productUpdater,
        $draftBuilder,
        $draftSaver,
        $eventDispatcher,
        CreateProposalCommand $command,
        ProductSubscriptionInterface $subscription,
        ProductInterface $product,
        FamilyInterface $family,
        EntityWithValuesDraftInterface $draft
    ) {
        $product->isVariant()->willReturn(false);
        $product->getCategoryCodes()->willReturn(['category_1']);
        $product->getFamily()->willReturn($family);
        $family->getAttributeCodes()->willReturn(['foo']);
        $subscription->getProduct()->willReturn($product);
        $command->getProductSubscription()->willReturn($subscription);

        $suggestedData = new SuggestedData(
            [
                'foo' => 'Lorem ipsum dolor sit amet',
            ]
        );
        $normlizedData = [
            'foo' => [
                [
                    'scope' => null,
                    'locale' => null,
                    'data' => 'Lorem ipsum dolor sit amet',
                ],
            ],
        ];

        $suggestedDataNormalizer->normalize($suggestedData)->willReturn($normlizedData);

        $subscription->getSuggestedData()->willReturn($suggestedData);
        $subscription->getProduct()->willReturn($product);
        $product->getFamily()->willReturn($family);
        $family->getAttributeCodes()->willReturn(['foo']);

        $productUpdater->update($product, ['values' => $normlizedData])->shouldBeCalled();
        $draftBuilder->build($product, Argument::cetera())->willReturn($draft);

        $eventDispatcher->dispatch(
            EntityWithValuesDraftEvents::PRE_READY,
            Argument::type(GenericEvent::class)
        )->shouldBeCalled();

        $draft->setAllReviewStatuses(EntityWithValuesDraftInterface::CHANGE_TO_REVIEW)->shouldBeCalled();
        $draftSaver->save($draft)->shouldBeCalled();

//        $eventDispatcher->dispatch(
//            EntityWithValuesDraftEvents::POST_READY,
//            new GenericEvent($draft)
//        )->shouldBeCalled();

        $this->handle($command)->shouldReturn(null);
    }
}
