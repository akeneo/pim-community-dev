<?php

namespace spec\Pim\Bundle\EnrichBundle\Saver;

use Akeneo\Component\StorageUtils\Saver\SavingOptionsResolverInterface;
use Doctrine\Common\Persistence\ObjectManager;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\EnrichBundle\Entity\MassEditJobConfiguration;
use Prophecy\Argument;

class MassEditConfigurationSaverSpec extends ObjectBehavior
{
    function let(
        ObjectManager $objectManager,
        SavingOptionsResolverInterface $optionsResolver

    ) {
        $this->beConstructedWith($objectManager, $optionsResolver);
    }

    function it_is_a_saver()
    {
        $this->shouldHaveType('Akeneo\Component\StorageUtils\Saver\SaverInterface');
    }

    function it_saves_a_mass_edit_job_configuration_and_flushes_by_default(
        $objectManager,
        $optionsResolver,
        MassEditJobConfiguration $massEditJobConf
    ) {
        $optionsResolver->resolveSaveOptions([])
            ->shouldBeCalled()
            ->willReturn(['flush' => true]);
        $objectManager->persist($massEditJobConf)->shouldBeCalled();
        $objectManager->flush()->shouldBeCalled();
        $this->save($massEditJobConf);
    }

    function it_saves_a_mass_edit_job_configuration_and_does_not_flushe(
        $objectManager,
        $optionsResolver,
        MassEditJobConfiguration $massEditJobConf
    ) {
        $optionsResolver->resolveSaveOptions(['flush' => false])
            ->shouldBeCalled()
            ->willReturn(['flush' => false]);
        $objectManager->persist($massEditJobConf)->shouldBeCalled();
        $objectManager->flush()->shouldNotBeCalled();
        $this->save($massEditJobConf, ['flush' => false]);
    }

    function it_throws_exception_when_save_anything_else_than_a_mass_edit_job_configuration()
    {
        $anythingElse = new \stdClass();
        $this
            ->shouldThrow(
                new \InvalidArgumentException(
                    sprintf(
                        'Expects a "Pim\Bundle\EnrichBundle\Entity\MassEditJobConfiguration", "%s" provided.',
                        get_class($anythingElse)
                    )
                )
            )
            ->during('save', [$anythingElse]);
    }
}
