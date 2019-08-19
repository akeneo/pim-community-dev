<?php
declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Factory\NonExistentValuesFilter;

use Akeneo\Pim\Enrichment\Component\Product\Factory\NonExistentValuesFilter\NonExistentFileValueFilter;
use Akeneo\Pim\Enrichment\Component\Product\Factory\NonExistentValuesFilter\NonExistentSimpleSelectValuesFilter;
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
        $ongoingFilteredRawValues = OnGoingFilteredRawValues::fromNonFilteredValuesCollectionIndexedByType(
            [
                AttributeTypes::IMAGE => [
                    'an_image' => [
                        [
                            'identifier' => 'product_A',
                            'values' => [
                                '<all_channels>' => [
                                    '<all_locales>' => 'imageA'
                                ],
                            ]
                        ],
                        [
                            'identifier' => 'product_B',
                            'values' => [
                                'ecommerce' => [
                                    'en_US' => 'imageB'
                                ],
                            ]
                        ]
                    ]
                ],
                AttributeTypes::FILE => [
                    'a_file' => [
                        [
                            'identifier' => 'product_A',
                            'values' => [
                                '<all_channels>' => [
                                    '<all_locales>' => 'fileA'
                                ],
                            ]
                        ],
                        [
                            'identifier' => 'product_B',
                            'values' => [
                                'ecommerce' => [
                                    'en_US' => 'fileB'
                                ],
                            ]
                        ]
                    ]
                ],
                AttributeTypes::TEXTAREA => [
                    'a_description' => [
                        [
                            'identifier' => 'product_B',
                            'values' => [
                                '<all_channels>' => [
                                    '<all_locales>' => 'plop'
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        );

        $fileA->getKey()->willReturn('fileA');
        $imageA->getKey()->willReturn('imageA');
        $fileB->getKey()->willReturn('fileB');
        $imageB->getKey()->willReturn('imageB');

        $images = ['imageA', 'imageB',];
        $files = ['fileA', 'fileB',];

        $fileInfoRepository->findBy(['key' => $images])->willReturn([$imageA, $imageB]);
        $fileInfoRepository->findBy(['key' => $files])->willReturn([$fileA, $fileB]);

        /** @var OnGoingFilteredRawValues $filteredCollection */
        $filteredCollection = $this->filter($ongoingFilteredRawValues);
        $filteredCollection->filteredRawValuesCollectionIndexedByType()->shouldBeLike(
            [
                AttributeTypes::IMAGE => [
                    'an_image' => [
                        [
                            'identifier' => 'product_A',
                            'values' => [
                                '<all_channels>' => [
                                    '<all_locales>' => $imageA
                                ],
                            ]
                        ],
                        [
                            'identifier' => 'product_B',
                            'values' => [
                                'ecommerce' => [
                                    'en_US' => $imageB
                                ],
                            ]
                        ]
                    ]
                ],
                AttributeTypes::FILE => [
                    'a_file' => [
                        [
                            'identifier' => 'product_A',
                            'values' => [
                                '<all_channels>' => [
                                    '<all_locales>' => $fileA
                                ],
                            ]
                        ],
                        [
                            'identifier' => 'product_B',
                            'values' => [
                                'ecommerce' => [
                                    'en_US' => $fileB
                                ],
                            ]
                        ]
                    ]
                ],
            ]
        );
    }
}
