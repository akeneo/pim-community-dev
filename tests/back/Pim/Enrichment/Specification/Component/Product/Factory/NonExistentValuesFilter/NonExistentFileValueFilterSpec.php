<?php
declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Factory\NonExistentValuesFilter;

use Akeneo\Pim\Enrichment\Component\Product\Factory\NonExistentValuesFilter\NonExistentFileValueFilter;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Enrichment\Component\Product\Factory\NonExistentValuesFilter\OnGoingFilteredRawValues;
use Akeneo\Tool\Component\FileStorage\Model\FileInfoInterface;
use Akeneo\Tool\Component\FileStorage\Repository\FileInfoRepositoryInterface;
use PhpSpec\ObjectBehavior;

/**
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class NonExistentFileValueFilterSpec extends ObjectBehavior
{
    public function let(FileInfoRepositoryInterface $fileInfoRepository) {
        $this->beConstructedWith($fileInfoRepository);
    }

    public function it_has_a_type()
    {
        $this->shouldHaveType(NonExistentFileValueFilter::class);
    }

    public function it_filters_file_and_image_values(
        FileInfoRepositoryInterface $fileInfoRepository,
        FileInfoInterface $fileA,
        FileInfoInterface $imageA,
        FileInfoInterface $fileB,
        FileInfoInterface $imageB
    ) {
        $ongoingFilteredRawValues = OnGoingFilteredRawValues::fromNonFilteredValuesCollectionIndexedByType([
            AttributeTypes::IMAGE => [
                'an_image' => [
                    [
                        'identifier' => 'product_A',
                        'values' => ['<all_channels>' => ['<all_locales>' => 'imageA']]
                    ], [
                        'identifier' => 'product_B',
                        'values' => ['ecommerce' => ['en_US' => 'imageB']]
                    ], [
                        'identifier' => 'product_C',
                        'values' => ['<all_channels>' => ['<all_locales>' => 'unexistingImage']]
                    ]
                ]
            ],
            AttributeTypes::FILE => [
                'a_file' => [
                    [
                        'identifier' => 'product_A',
                        'values' => ['<all_channels>' => ['<all_locales>' => 'fileA']]
                    ], [
                        'identifier' => 'product_B',
                        'values' => ['ecommerce' => ['en_US' => 'fileB']]
                    ], [
                        'identifier' => 'product_C',
                        'values' => ['<all_channels>' => ['<all_locales>' => 'unexistingFile']]
                    ]
                ]
            ],
            AttributeTypes::TEXTAREA => [
                'a_description' => [
                    [
                        'identifier' => 'product_B',
                        'values' => ['<all_channels>' => ['<all_locales>' => 'plop']]
                    ]
                ]
            ]
        ]);

        $fileA->getKey()->willReturn('fileA');
        $fileB->getKey()->willReturn('fileB');
        $imageA->getKey()->willReturn('imageA');
        $imageB->getKey()->willReturn('imageB');

        $fileInfoRepository->findBy(['key' => ['fileA', 'fileB', 'unexistingFile']])->willReturn([$fileA, $fileB]);
        $fileInfoRepository->findBy(['key' => ['imageA', 'imageB', 'unexistingImage']])->willReturn([$imageA, $imageB]);

        /** @var OnGoingFilteredRawValues $filteredCollection */
        $filteredCollection = $this->filter($ongoingFilteredRawValues);
        $filteredCollection->filteredRawValuesCollectionIndexedByType()->shouldBeLike([
            AttributeTypes::IMAGE => [
                'an_image' => [
                    [
                        'identifier' => 'product_A',
                        'values' => ['<all_channels>' => ['<all_locales>' => $imageA]]
                    ], [
                        'identifier' => 'product_B',
                        'values' => ['ecommerce' => ['en_US' => $imageB]]
                    ], [
                        'identifier' => 'product_C',
                        'values' => ['<all_channels>' => ['<all_locales>' => null]]
                    ]
                ]
            ],
            AttributeTypes::FILE => [
                'a_file' => [
                    [
                        'identifier' => 'product_A',
                        'values' => ['<all_channels>' => ['<all_locales>' => $fileA]]
                    ], [
                        'identifier' => 'product_B',
                        'values' => ['ecommerce' => ['en_US' => $fileB]]
                    ], [
                        'identifier' => 'product_C',
                        'values' => ['<all_channels>' => ['<all_locales>' => null]]
                    ]
                ]
            ],
        ]);
    }
}
