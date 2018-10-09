<?php

namespace Specification\Akeneo\Platform\Bundle\UIBundle\Form\Transformer;

use Akeneo\Platform\Bundle\UIBundle\Form\Transformer\AjaxReferenceDataTransformer;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Product\ReferenceData\LabelRenderer;
use Akeneo\Pim\Enrichment\Component\Product\Model\ReferenceDataInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ReferenceDataRepositoryInterface;
use Prophecy\Argument;

class AjaxReferenceDataTransformerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(AjaxReferenceDataTransformer::class);
    }

    function let(ReferenceDataRepositoryInterface $repository, LabelRenderer $renderer)
    {
        $this->beConstructedWith($repository, $renderer, ['multiple' => false]);
    }

    function it_reverse_transforms_a_single_value($repository, ReferenceDataInterface $referenceData)
    {
        $repository->find(15)->willReturn($referenceData);

        $this->reverseTransform(15)->shouldReturn($referenceData);
    }

    function it_reverse_transforms_multiple_values(
        $repository,
        $renderer,
        ReferenceDataInterface $referenceData1,
        ReferenceDataInterface $referenceData2
    ) {
        $this->beConstructedWith($repository, $renderer, ['multiple' => true]);

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
        $renderer,
        ReferenceDataInterface $referenceData1,
        ReferenceDataInterface $referenceData2
    ) {
        $this->beConstructedWith($repository, $renderer, ['multiple' => true]);

        $referenceData1->getId()->willReturn(007);
        $referenceData2->getId()->willReturn(41);

        $this->transform([$referenceData1, $referenceData2])->shouldReturn('7,41');
    }

    function it_get_the_label_of_a_single_value($renderer, ReferenceDataInterface $referenceData)
    {
        $renderer->render(Argument::any())->willReturn('[Good luck]');
        $referenceData->getId()->willReturn(13);

        $this->getOptions($referenceData)->shouldReturn(['id' => 13, 'text' => '[Good luck]']);
    }

    function it_get_the_labels_of_multiple_values(
        $repository,
        $renderer,
        ReferenceDataInterface $referenceData1,
        ReferenceDataInterface $referenceData2
    ) {
        $this->beConstructedWith($repository, $renderer, ['multiple' => true]);

        $referenceData1->getId()->willReturn(13);
        $renderer->render($referenceData1)->willReturn('[Good luck]');

        $referenceData2->getId()->willReturn(456);
        $renderer->render($referenceData2)->willReturn('[Random label]');

        $this->getOptions([$referenceData1, $referenceData2])->shouldReturn([
            ['id' => 13, 'text' => '[Good luck]'],
            ['id' => 456, 'text' => '[Random label]'],
        ]);
    }
}

class ReferenceDataColorWithLabel implements ReferenceDataInterface
{
    public function getId()
    {
    }
    public function getCode()
    {
    }
    public function setCode($code)
    {
    }
    public function getSortOrder()
    {
    }
    public function getName()
    {
    }
    public static function getLabelProperty()
    {
        return 'name';
    }
    public function __toString()
    {
    }
}
