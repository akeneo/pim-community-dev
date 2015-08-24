<?php

namespace spec\PimEnterprise\Component\ProductAsset;

use PhpSpec\ObjectBehavior;
use PimEnterprise\Component\ProductAsset\Exception\LockedVariationGenerationException;
use PimEnterprise\Component\ProductAsset\Model\VariationInterface;
use PimEnterprise\Component\ProductAsset\ProcessedItem;
use PimEnterprise\Component\ProductAsset\VariationFileGeneratorInterface;
use Prophecy\Argument;

class VariationsCollectionFilesGeneratorSpec extends ObjectBehavior
{
    public function let(VariationFileGeneratorInterface $variationFileGenerator)
    {
        $this->beConstructedWith($variationFileGenerator);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('PimEnterprise\Component\ProductAsset\VariationsCollectionFilesGenerator');
        $this->shouldImplement('PimEnterprise\Component\ProductAsset\VariationsCollectionFilesGeneratorInterface');
    }

    function it_generates_the_variation_files_from_a_reference(
        $variationFileGenerator,
        VariationInterface $variation1,
        VariationInterface $variation2,
        VariationInterface $variation3
    ) {
        $variation1->isLocked()->willReturn(false);
        $variation2->isLocked()->willReturn(false);
        $variation3->isLocked()->willReturn(true);

        $variationFileGenerator->generate($variation2)->willThrow(new \LogicException('Impossible to build the variation'));

        $variationFileGenerator->generate($variation1)->shouldBeCalled();
        $variationFileGenerator->generate($variation2)->shouldBeCalled();
        $variationFileGenerator->generate($variation3)->shouldNotBeCalled();

        $res = $this->generate([$variation1, $variation2, $variation3]);
        $res->shouldReturnAnInstanceOf('PimEnterprise\Component\ProductAsset\ProcessedItemList');
        $res->shouldBeListOfProcessedVariations();
    }

    function it_generates_locked_variation_files_from_a_reference(
        $variationFileGenerator,
        VariationInterface $variation1,
        VariationInterface $variation2
    ) {
        $variation1->isLocked()->willReturn(false);
        $variation2->isLocked()->willReturn(true);

        $variationFileGenerator->generate($variation1)->shouldBeCalled();
        $variationFileGenerator->generate($variation2)->shouldBeCalled();

        $res = $this->generate([$variation1, $variation2], true);
        $res->shouldReturnAnInstanceOf('PimEnterprise\Component\ProductAsset\ProcessedItemList');
        $res->shouldHaveCount(2);
        $res->shouldBeListOfSuccessfulProcessedVariations();
    }

    public function getMatchers()
    {
        return [
            'beListOfProcessedVariations' => function($subject) {
                return 3 === count($subject) &&
                    ProcessedItem::STATE_SUCCESS === $subject[0]->getState() &&
                    ProcessedItem::STATE_ERROR === $subject[1]->getState() &&
                    ProcessedItem::STATE_SKIPPED === $subject[2]->getState() &&
                    'Impossible to build the variation' === $subject[1]->getException()->getMessage() &&
                    $subject[2]->getException() instanceof LockedVariationGenerationException;
            },
            'beListOfSuccessfulProcessedVariations' => function($processedlist) {
                $success = true;
                foreach ($processedlist as $item) {
                    if (ProcessedItem::STATE_SUCCESS !== $item->getState()) {
                        $success = false;
                        break;
                    }
                }
                return $success;
            },
        ];
    }
}
