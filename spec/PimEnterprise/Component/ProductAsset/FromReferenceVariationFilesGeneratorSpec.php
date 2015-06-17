<?php

namespace spec\PimEnterprise\Component\ProductAsset;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use PhpSpec\ObjectBehavior;
use PimEnterprise\Component\ProductAsset\Model\ReferenceInterface;
use PimEnterprise\Component\ProductAsset\Model\VariationInterface;
use PimEnterprise\Component\ProductAsset\ProcessedItem;
use PimEnterprise\Component\ProductAsset\ProcessedItemList;
use PimEnterprise\Component\ProductAsset\VariationFileGeneratorInterface;
use Prophecy\Argument;

class FromReferenceVariationFilesGeneratorSpec extends ObjectBehavior
{
    public function let(VariationFileGeneratorInterface $variationFileGenerator)
    {
        $this->beConstructedWith($variationFileGenerator);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('PimEnterprise\Component\ProductAsset\FromReferenceVariationFilesGenerator');
    }

    function it_generates_the_variation_files_from_a_reference(
        $variationFileGenerator,
        ReferenceInterface $reference,
        VariationInterface $variation1,
        VariationInterface $variation2,
        VariationInterface $variation3
    ) {
        $variation1->isLocked()->willReturn(false);
        $variation2->isLocked()->willReturn(false);
        $variation3->isLocked()->willReturn(true);

        $reference->getVariations()->willReturn([$variation1, $variation2, $variation3]);

        $variationFileGenerator->generate($variation2)->willThrow(new \LogicException('Impossible to build the variation'));

        $variationFileGenerator->generate($variation1)->shouldBeCalled();
        $variationFileGenerator->generate($variation2)->shouldBeCalled();
        $variationFileGenerator->generate($variation3)->shouldNotBeCalled();

        $res = $this->generate($reference);
        $res->shouldReturnAnInstanceOf('PimEnterprise\Component\ProductAsset\ProcessedItemList');
        $res->shouldBeListOfProcessedVariations();
    }

    public function getMatchers()
    {
        return [
            'beListOfProcessedVariations' => function($subject) {
                /** @var ProcessedItemList $subject */
                return 3 === count($subject) &&
                    ProcessedItem::STATE_SUCCESS === $subject[0]->getState() &&
                    ProcessedItem::STATE_ERROR === $subject[1]->getState() &&
                    ProcessedItem::STATE_SKIPPED === $subject[2]->getState() &&
                    'Impossible to build the variation' === $subject[1]->getReason() &&
                    'The variation is locked' === $subject[2]->getReason();
            },
        ];
    }
}
