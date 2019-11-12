<?php
declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Factory;

use Akeneo\Pim\Enrichment\Component\Product\Exception\InvalidAttributeException;
use Akeneo\Pim\Enrichment\Component\Product\Factory\Value\ValueFactory as SingleValueFactory;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use OutOfBoundsException;
use PhpSpec\ObjectBehavior;

/**
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ValueFactorySpec extends ObjectBehavior
{
    public function let(
        SingleValueFactory $factory1,
        SingleValueFactory $factory2
    ) {
        $factory1->supportedAttributeType()->willReturn('an_attribute_type1');
        $factory2->supportedAttributeType()->willReturn('an_attribute_type2');
        $this->beConstructedWith([$factory1, $factory2]);
    }

    public function it_calls_the_right_factory_without_checking_data(SingleValueFactory $factory2, ValueInterface $value)
    {
        $attribute = new Attribute('an_attribute', 'an_attribute_type2', [], false, false, null, false, 'backend_type', []);
        $factory2->createWithoutCheckingData($attribute, null, null, 'data')->willReturn($value);
        $this->createWithoutCheckingData($attribute, null, null, 'data')->shouldReturn($value);
    }

    public function it_calls_the_right_factory_by_checking_data(SingleValueFactory $factory2, ValueInterface $value)
    {
        $attribute = new Attribute('an_attribute', 'an_attribute_type2', [], false, false, null, false, 'backend_type', []);
        $factory2->createByCheckingData($attribute, null, null, 'data')->willReturn($value);
        $this->createByCheckingData($attribute, null, null, 'data')->shouldReturn($value);
    }

    public function it_throws_an_exception_if_the_attribute_type_is_not_supported()
    {
        $this->shouldThrow(OutOfBoundsException::class)->during(
            'createWithoutCheckingData',
            [
                new Attribute('an_attribute', 'non_supported_attribute_type', [], false, false, null, false, 'backend_type', []),
                null,
                null,
                'data'
            ]
        );
    }

    public function it_throws_an_exception_if_attribute_is_not_consistent_with_provided_locale_code_or_channel_code()
    {
        $this->shouldThrow(InvalidAttributeException::class)->during(
            'createByCheckingData',
            [
                new Attribute('an_attribute', 'an_attribute_type1', [], false, false, null, false, 'backend_type', []),
                'ecommerce',
                null,
                'data'
            ]
        );
    }
}
