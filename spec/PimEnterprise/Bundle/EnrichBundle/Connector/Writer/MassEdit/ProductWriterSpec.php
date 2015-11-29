<?php

namespace spec\PimEnterprise\Bundle\EnrichBundle\Connector\Writer\MassEdit;

use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Akeneo\Component\StorageUtils\Detacher\BulkObjectDetacherInterface;
use Akeneo\Component\StorageUtils\Saver\BulkSaverInterface;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Bundle\VersioningBundle\Manager\VersionManager;
use PimEnterprise\Bundle\SecurityBundle\Attributes;
use Prophecy\Argument;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class ProductWriterSpec extends ObjectBehavior
{
    function let(
        VersionManager $versionManager,
        BulkSaverInterface $productSaver,
        BulkObjectDetacherInterface $detacher,
        AuthorizationCheckerInterface $authorizationChecker
    ) {
        $this->beConstructedWith(
            $versionManager,
            $productSaver,
            $detacher,
            $authorizationChecker
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('\Pim\Component\Connector\Writer\Doctrine\ProductWriter');
    }

    function it_is_an_item_writer()
    {
        $this->shouldHaveType('\Akeneo\Bundle\BatchBundle\Item\ItemWriterInterface');
    }

    function it_is_step_execution_aware()
    {
        $this->shouldHaveType('\Akeneo\Bundle\BatchBundle\Step\StepExecutionAwareInterface');
    }

    function it_is_a_configurable_step_element()
    {
        $this->shouldHaveType('\Akeneo\Bundle\BatchBundle\Item\AbstractConfigurableStepElement');
    }

    function it_provides_configuration_fields()
    {
        $this->getConfigurationFields()->shouldReturn([
            'realTimeVersioning' => [
                'type'    => 'switch',
                'options' => [
                    'label' => 'pim_connector.import.realTimeVersioning.label',
                    'help'  => 'pim_connector.import.realTimeVersioning.help'
                ]
            ]
        ]);
    }

    function it_is_configurable()
    {
        $this->isRealTimeVersioning()->shouldReturn(true);

        $this->setRealTimeVersioning(false);

        $this->isRealTimeVersioning()->shouldReturn(false);
    }

    function it_saves_items(
        $productSaver,
        StepExecution $stepExecution,
        ProductInterface $product1,
        ProductInterface $product2
    ) {
        $items = [$product1, $product2];

        $product1->getId()->willReturn('45');
        $product2->getId()->willReturn(null);

        $this->setStepExecution($stepExecution);
        $productSaver->saveAll($items, ['recalculate' => false])->shouldBeCalled();
        $this->write($items);
    }

    function it_increments_summary_info_with_permission(
        $authorizationChecker,
        TokenStorageInterface $tokenStorage,
        StepExecution $stepExecution,
        ProductInterface $product1,
        ProductInterface $product2,
        ProductInterface $product3,
        ProductInterface $product4
    ) {
        $product1->getId()->willReturn('45');
        $tokenStorage->getToken()->willReturn('token');
        $product2->getId()->willReturn(null);
        $product3->getId()->willReturn('42');
        $product4->getId()->willReturn(null);

        $authorizationChecker->isGranted(Attributes::OWN, Argument::any())->willReturn(false);

        $stepExecution->incrementSummaryInfo('process')->shouldBeCalledTimes(2);
        $stepExecution->incrementSummaryInfo('proposal')->shouldBeCalledTimes(2);

        $this->setStepExecution($stepExecution);
        $this->write([$product1, $product2, $product3, $product4]);
    }
}
