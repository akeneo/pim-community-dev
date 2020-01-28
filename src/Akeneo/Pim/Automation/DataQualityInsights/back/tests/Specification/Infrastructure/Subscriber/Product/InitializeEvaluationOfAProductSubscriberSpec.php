<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Subscriber\Product;

use Akeneo\Pim\Automation\DataQualityInsights\Application\CriteriaEvaluation\CreateProductsCriteriaEvaluations;
use Akeneo\Pim\Automation\DataQualityInsights\Application\FeatureFlag;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Connector\JobParameters\EvaluateProductsCriteriaParameters;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Connector\Tasklet\EvaluateProductsCriteriaTasklet;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Symfony\Events\TitleSuggestionIgnoredEvent;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Symfony\Events\WordIgnoredEvent;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Tool\Bundle\BatchBundle\Job\JobInstanceRepository;
use Akeneo\Tool\Bundle\BatchBundle\Launcher\JobLauncherInterface;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class InitializeEvaluationOfAProductSubscriberSpec extends ObjectBehavior
{
    public function let(
        FeatureFlag $dataQualityInsightsFeature,
        CreateProductsCriteriaEvaluations $createProductsCriteriaEvaluations,
        JobLauncherInterface $queueJobLauncher,
        JobInstanceRepository $jobInstanceRepository,
        TokenStorageInterface $tokenStorage,
        LoggerInterface $logger
    ) {
        $this->beConstructedWith(
            $dataQualityInsightsFeature,
            $createProductsCriteriaEvaluations,
            $queueJobLauncher,
            $jobInstanceRepository,
            $tokenStorage,
            $logger
        );
    }

    public function it_is_an_event_subscriber(): void
    {
        $this->shouldImplement(EventSubscriberInterface::class);
    }

    public function it_subscribes_to_several_events(): void
    {
        $this::getSubscribedEvents()->shouldHaveKey(WordIgnoredEvent::WORD_IGNORED);
        $this::getSubscribedEvents()->shouldHaveKey(StorageEvents::POST_SAVE);
        $this::getSubscribedEvents()->shouldHaveKey(StorageEvents::POST_SAVE_ALL);
    }

    public function it_schedule_evaluation_when_a_title_suggestion_is_ignored(
        $dataQualityInsightsFeature,
        $createProductsCriteriaEvaluations,
        $jobInstanceRepository,
        $tokenStorage,
        $queueJobLauncher,
        ProductInterface $product,
        JobInstance $jobInstance,
        TokenInterface $token,
        UserInterface $user
    ) {
        $product->getId()->willReturn(12345);
        $dataQualityInsightsFeature->isEnabled()->willReturn(true);
        $createProductsCriteriaEvaluations->create([new ProductId(12345)])->shouldBeCalled();

        $jobInstanceRepository
            ->findOneByIdentifier(EvaluateProductsCriteriaTasklet::JOB_INSTANCE_NAME)
            ->willReturn($jobInstance);

        $tokenStorage->getToken()->willReturn($token);
        $token->getUser()->willReturn($user);

        $queueJobLauncher->launch(
            $jobInstance,
            $user,
            [EvaluateProductsCriteriaParameters::PRODUCT_IDS => [12345]]
        );

        $this->onIgnoredTitleSuggestion(new TitleSuggestionIgnoredEvent(new ProductId(12345)));
    }

    public function it_schedule_evaluation_when_a_word_is_ignored(
        $dataQualityInsightsFeature,
        $createProductsCriteriaEvaluations,
        $jobInstanceRepository,
        $tokenStorage,
        $queueJobLauncher,
        ProductInterface $product,
        JobInstance $jobInstance,
        TokenInterface $token,
        UserInterface $user
    ) {
        $product->getId()->willReturn(12345);
        $dataQualityInsightsFeature->isEnabled()->willReturn(true);
        $createProductsCriteriaEvaluations->create([new ProductId(12345)])->shouldBeCalled();

        $jobInstanceRepository
            ->findOneByIdentifier(EvaluateProductsCriteriaTasklet::JOB_INSTANCE_NAME)
            ->willReturn($jobInstance);

        $tokenStorage->getToken()->willReturn($token);
        $token->getUser()->willReturn($user);

        $queueJobLauncher->launch(
            $jobInstance,
            $user,
            [EvaluateProductsCriteriaParameters::PRODUCT_IDS => [12345]]
        );

        $this->onIgnoredWord(new WordIgnoredEvent(new ProductId(12345)));
    }

    public function it_does_nothing_when_the_entity_is_not_a_product($dataQualityInsightsFeature, $createProductsCriteriaEvaluations)
    {
        $dataQualityInsightsFeature->isEnabled()->shouldNotBeCalled();
        $createProductsCriteriaEvaluations->create(Argument::any())->shouldNotBeCalled();
        $this->onPostSave(new GenericEvent(new \stdClass()));
    }

    public function it_does_nothing_when_data_quality_insights_feature_is_not_active(
        $dataQualityInsightsFeature,
        $createProductsCriteriaEvaluations,
        ProductInterface $product
    ) {
        $dataQualityInsightsFeature->isEnabled()->willReturn(false);
        $createProductsCriteriaEvaluations->create(Argument::any())->shouldNotBeCalled();
        $product->isVariant()->willReturn(false);

        $this->onPostSave(new GenericEvent($product->getWrappedObject()));
    }

    public function it_does_nothing_when_product_is_a_variant(
        $dataQualityInsightsFeature,
        $createProductsCriteriaEvaluations,
        ProductInterface $product
    ) {
        $dataQualityInsightsFeature->isEnabled()->willReturn(false);
        $createProductsCriteriaEvaluations->create(Argument::any())->shouldNotBeCalled();
        $product->isVariant()->willReturn(true);

        $this->onPostSave(new GenericEvent($product->getWrappedObject()));
    }

    public function it_does_nothing_on_non_unitary_post_save(
        $dataQualityInsightsFeature,
        $createProductsCriteriaEvaluations,
        ProductInterface $product
    ): void {
        $dataQualityInsightsFeature->isEnabled()->shouldNotBeCalled();
        $createProductsCriteriaEvaluations->create(Argument::any())->shouldNotBeCalled();
        $product->isVariant()->willReturn(false);

        $this->onPostSave(new GenericEvent($product->getWrappedObject(), ['unitary' => false]));
        $this->onPostSave(new GenericEvent($product->getWrappedObject(), []));
    }

    public function it_creates_criteria_on_unitary_product_post_save(
        $dataQualityInsightsFeature,
        $createProductsCriteriaEvaluations,
        $jobInstanceRepository,
        $tokenStorage,
        $queueJobLauncher,
        ProductInterface $product,
        JobInstance $jobInstance,
        TokenInterface $token,
        UserInterface $user
    ) {
        $product->getId()->willReturn(12345);
        $product->isVariant()->willReturn(false);
        $dataQualityInsightsFeature->isEnabled()->willReturn(true);
        $createProductsCriteriaEvaluations->create([new ProductId(12345)])->shouldBeCalled();

        $jobInstanceRepository
            ->findOneByIdentifier(EvaluateProductsCriteriaTasklet::JOB_INSTANCE_NAME)
            ->willReturn($jobInstance);

        $tokenStorage->getToken()->willReturn($token);
        $token->getUser()->willReturn($user);

        $queueJobLauncher->launch(
            $jobInstance,
            $user,
            [EvaluateProductsCriteriaParameters::PRODUCT_IDS => [12345]]
        );

        $this->onPostSave(new GenericEvent($product->getWrappedObject(), ['unitary' => true]));
    }

    public function it_does_nothing_when_one_entity_is_not_a_product_on_post_save_all(
        $dataQualityInsightsFeature,
        $createProductsCriteriaEvaluations,
        $jobInstanceRepository,
        $tokenStorage,
        $queueJobLauncher,
        ProductInterface $product,
        JobInstance $jobInstance,
        TokenInterface $token,
        UserInterface $user
    ) {
        $product->getId()->willReturn(12345);
        $product->isVariant()->willReturn(false);
        $dataQualityInsightsFeature->isEnabled()->willReturn(true);
        $createProductsCriteriaEvaluations->create([new ProductId(12345)])->shouldBeCalled();

        $jobInstanceRepository
            ->findOneByIdentifier(EvaluateProductsCriteriaTasklet::JOB_INSTANCE_NAME)
            ->willReturn($jobInstance);

        $tokenStorage->getToken()->willReturn($token);
        $token->getUser()->willReturn($user);

        $queueJobLauncher->launch(
            $jobInstance,
            $user,
            [EvaluateProductsCriteriaParameters::PRODUCT_IDS => [12345]]
        );

        $this->onPostSaveAll(new GenericEvent([new \stdClass(), $product->getWrappedObject()]));
    }


    public function it_does_nothing_when_data_quality_insights_is_not_active_on_post_save_all(
        $dataQualityInsightsFeature,
        $createProductsCriteriaEvaluations,
        ProductInterface $product1,
        ProductInterface $product2
    ) {
        $product1->getId()->willReturn(12345);
        $product2->getId()->willReturn(67891);
        $product1->isVariant()->willReturn(false);
        $product2->isVariant()->willReturn(false);
        $dataQualityInsightsFeature->isEnabled()->willReturn(false);
        $createProductsCriteriaEvaluations->create(Argument::any())->shouldNotBeCalled();

        $this->onPostSaveAll(new GenericEvent([$product1->getWrappedObject(), $product2->getWrappedObject()]));
    }

    public function it_does_nothing_when_products_are_variant_on_post_save_all(
        $dataQualityInsightsFeature,
        $createProductsCriteriaEvaluations,
        ProductInterface $product1,
        ProductInterface $product2
    ) {
        $product1->getId()->willReturn(12345);
        $product2->getId()->willReturn(67891);
        $product1->isVariant()->willReturn(true);
        $product2->isVariant()->willReturn(true);
        $dataQualityInsightsFeature->isEnabled()->willReturn(true);
        $createProductsCriteriaEvaluations->create(Argument::any())->shouldNotBeCalled();

        $this->onPostSaveAll(new GenericEvent([$product1->getWrappedObject(), $product2->getWrappedObject()]));
    }

    public function it_creates_criteria_on_post_save_all(
        $dataQualityInsightsFeature,
        $createProductsCriteriaEvaluations,
        $jobInstanceRepository,
        $tokenStorage,
        $queueJobLauncher,
        ProductInterface $product1,
        ProductInterface $product2,
        JobInstance $jobInstance,
        TokenInterface $token,
        UserInterface $user
    ) {
        $product1->getId()->willReturn(12345);
        $product2->getId()->willReturn(67891);
        $product1->isVariant()->willReturn(false);
        $product2->isVariant()->willReturn(false);
        $dataQualityInsightsFeature->isEnabled()->willReturn(true);
        $createProductsCriteriaEvaluations->create([new ProductId(12345), new ProductId(67891)])->shouldBeCalled();

        $jobInstanceRepository
            ->findOneByIdentifier(EvaluateProductsCriteriaTasklet::JOB_INSTANCE_NAME)
            ->willReturn($jobInstance);

        $tokenStorage->getToken()->willReturn($token);
        $token->getUser()->willReturn($user);

        $queueJobLauncher->launch(
            $jobInstance,
            $user,
            [EvaluateProductsCriteriaParameters::PRODUCT_IDS => [12345, 67891]]
        );

        $this->onPostSaveAll(new GenericEvent([$product1->getWrappedObject(), $product2->getWrappedObject()]));
    }

    public function it_does_not_stop_the_process_if_something_goes_wrong(
        $dataQualityInsightsFeature,
        $createProductsCriteriaEvaluations,
        $jobInstanceRepository,
        $logger,
        ProductInterface $product
    ) {
        $product->getId()->willReturn(12345);
        $product->isVariant()->willReturn(false);
        $dataQualityInsightsFeature->isEnabled()->willReturn(true);
        $createProductsCriteriaEvaluations->create([new ProductId(12345)])->shouldBeCalled();

        $jobInstanceRepository
            ->findOneByIdentifier(EvaluateProductsCriteriaTasklet::JOB_INSTANCE_NAME)
            ->willReturn(null);

        $logger->error(Argument::any())->shouldBeCalledOnce();

        $this->onPostSave(new GenericEvent($product->getWrappedObject(), ['unitary' => true]));
    }
}
