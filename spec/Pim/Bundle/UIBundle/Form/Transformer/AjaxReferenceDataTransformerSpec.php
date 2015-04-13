<?php

namespace spec\Pim\Bundle\UIBundle\Form\Transformer;

use PhpSpec\ObjectBehavior;
use Pim\Component\ReferenceData\Model\ReferenceDataInterface;
use Pim\Component\ReferenceData\Repository\ReferenceDataRepositoryInterface;
use Prophecy\Argument;

class AjaxReferenceDataTransformerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\UIBundle\Form\Transformer\AjaxReferenceDataTransformer');
    }

    function let(ReferenceDataRepositoryInterface $repository)
    {
        $this->beConstructedWith($repository, ['multiple' => false]);
    }

    function it_reverse_transforms_a_single_value($repository, ReferenceDataInterface $referenceData)
    {
        $repository->find(15)->willReturn($referenceData);

        $this->reverseTransform(15)->shouldReturn($referenceData);
    }

    function it_reverse_transforms_multiple_values(
        $repository,
        ReferenceDataInterface $referenceData1,
        ReferenceDataInterface $referenceData2
    ) {
        $this->beConstructedWith($repository, ['multiple' => true]);

        $repository->find(42)->willReturn($referenceData1);
        $repository->find(69)->willReturn($referenceData2);

        $this->reverseTransform('42,69')->shouldReturn([$referenceData1, $referenceData2]);
    }

    function it_transforms_a_single_value(ReferenceDataInterface $referenceData)
    {
        $referenceData->getId()->willReturn(666);

        $this->transform($referenceData)->shouldReturn(666);
    }

    function it_transforms_multiple_values(
        $repository,
        ReferenceDataInterface $referenceData1,
        ReferenceDataInterface $referenceData2
    ) {
        $this->beConstructedWith($repository, ['multiple' => true]);

        $referenceData1->getId()->willReturn(007);
        $referenceData2->getId()->willReturn(41);

        $this->transform([$referenceData1, $referenceData2])->shouldReturn('7,41');
    }

    function it_get_the_label_of_a_single_value(ReferenceDataInterface $referenceData)
    {
        $referenceData->getId()->willReturn(13);
        $referenceData->getCode()->willReturn('Good luck');

        $this->getOptions($referenceData)->shouldReturn(['id' => 13, 'text' => '[Good luck]']);
    }

    function it_get_the_labels_of_multiple_values(
        $repository,
        ReferenceDataInterface $referenceData1,
        ReferenceDataInterface $referenceData2
    ) {
        $this->beConstructedWith($repository, ['multiple' => true]);

        $referenceData1->getId()->willReturn(13);
        $referenceData1->getCode()->willReturn('Good luck');

        $referenceData2->getId()->willReturn(456);
        $referenceData2->getCode()->willReturn('Random label');

        $this->getOptions([$referenceData1, $referenceData2])->shouldReturn([
            ['id' => 13, 'text' => '[Good luck]'],
            ['id' => 456, 'text' => '[Random label]'],
        ]);
    }


}
