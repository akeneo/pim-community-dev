<?php
declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Factory\EmptyValuesCleaner;

use Akeneo\Pim\Enrichment\Component\Product\Factory\EmptyValuesCleaner\ChainedEmptyValuesCleanerInterface;
use Akeneo\Pim\Enrichment\Component\Product\Factory\EmptyValuesCleaner\EmptyValuesCleaner;
use Akeneo\Pim\Enrichment\Component\Product\Factory\EmptyValuesCleaner\OnGoingCleanedRawValues;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use PhpSpec\ObjectBehavior;

/**
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ChainedEmptyValuesCleanerSpec extends ObjectBehavior
{
    public function let(EmptyValuesCleaner $cleaner1, EmptyValuesCleaner $cleaner2)
    {
        $this->beConstructedWith([$cleaner1, $cleaner2]);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(ChainedEmptyValuesCleanerInterface::class);
    }

    public function it_calls_every_empty_values_cleaner_and_it_copies_the_remaining_values(
        EmptyValuesCleaner $cleaner1,
        EmptyValuesCleaner $cleaner2
    ) {
        $nonCleanedRawValues = [
            AttributeTypes::TEXT => [
                'description' => [
                    [
                        'identifier' => 'productA',
                        'values' => [
                            '<all_channels>' => [
                                '<all_locales>' => 'a description'
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $ongoingRawValues = new OnGoingCleanedRawValues([], $nonCleanedRawValues);
        $cleaner1->clean($ongoingRawValues)->willReturn($ongoingRawValues);
        $cleaner2->clean($ongoingRawValues)->willReturn($ongoingRawValues);

        $onGoingCleanedRawValues = new OnGoingCleanedRawValues($nonCleanedRawValues, []);

        $this->cleanAll($ongoingRawValues)->shouldBeLike($onGoingCleanedRawValues);
    }
}
