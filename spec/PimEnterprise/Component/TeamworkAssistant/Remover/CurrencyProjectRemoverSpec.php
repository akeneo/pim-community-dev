<?php

namespace spec\PimEnterprise\Component\TeamworkAssistant\Remover;

use Akeneo\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use Akeneo\Component\StorageUtils\Remover\RemoverInterface;
use Akeneo\Component\StorageUtils\StorageEvents;
use Doctrine\Common\Collections\ArrayCollection;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\CurrencyInterface;
use Pim\Component\Catalog\Model\LocaleInterface;
use PimEnterprise\Component\TeamworkAssistant\Model\ProjectInterface;
use PimEnterprise\Component\TeamworkAssistant\Remover\CurrencyProjectRemover;
use PimEnterprise\Component\TeamworkAssistant\Remover\ProjectRemoverInterface;
use PimEnterprise\Component\TeamworkAssistant\Repository\ProjectRepositoryInterface;
use Prophecy\Argument;

class CurrencyProjectRemoverSpec extends ObjectBehavior
{
    function let(
        ProjectRepositoryInterface $projectRepository,
        RemoverInterface $projectRemover,
        ObjectDetacherInterface $detacher
    ) {
        $this->beConstructedWith($projectRepository, $projectRemover, $detacher);
    }

    function it_is_a_project_remover()
    {
        $this->shouldHaveType(CurrencyProjectRemover::class);
        $this->shouldImplement(ProjectRemoverInterface::class);
    }

    function it_removes_projects_if_currency_used_as_product_filter_is_no_longer_part_of_its_channel_and_detach_others(
        $projectRepository,
        $projectRemover,
        $detacher,
        ProjectInterface $firstProject,
        ProjectInterface $secondProject,
        ChannelInterface $mobileChannel,
        ArrayCollection $currencies,
        CurrencyInterface $eur
    ) {
        $projectRepository->findByChannel($mobileChannel)->willReturn([$firstProject, $secondProject]);

        $eur->getCode()->willReturn('EUR');

        $mobileChannel->getCurrencies()->willReturn($currencies);
        $currencies->map(Argument::any())->willReturn($currencies);
        $currencies->contains('USD')->willReturn(true);
        $currencies->contains('EUR')->willReturn(false);

        $firstProject->getProductFilters()->willReturn([
            [
                'field' => 'price',
                'operator' => '>',
                'value' => ['amount' => '13', 'currency' => 'USD']
            ],
        ]);
        $secondProject->getProductFilters()->willReturn([
            [
                'field' => 'price',
                'operator' => '>',
                'value' => ['amount' => '42', 'currency' => 'EUR']
            ],
            [
                'field' => 'price',
                'operator' => '>',
                'value' => ['amount' => '13', 'currency' => 'USD']
            ],
        ]);

        $projectRemover->remove($firstProject)->shouldNotBeCalled();
        $projectRemover->remove($secondProject)->shouldBeCalled();

        $detacher->detach($firstProject)->shouldBeCalled();
        $detacher->detach($secondProject)->shouldNotBeCalled();

        $this->removeProjectsImpactedBy($mobileChannel);
    }

    function it_removes_impacted_projects_only_for_channel_post_save(
        LocaleInterface $locale,
        ChannelInterface $channel
    ) {
        $this->isSupported($locale, StorageEvents::PRE_REMOVE)->shouldReturn(false);
        $this->isSupported($locale, StorageEvents::POST_SAVE)->shouldReturn(false);
        $this->isSupported($channel, StorageEvents::PRE_REMOVE)->shouldReturn(false);
        $this->isSupported($channel, StorageEvents::POST_SAVE)->shouldReturn(true);
    }
}
