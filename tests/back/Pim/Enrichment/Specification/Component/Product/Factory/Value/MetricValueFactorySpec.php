<?php
declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Factory\Value;

use Akeneo\Pim\Enrichment\Component\Product\Factory\MetricFactory;
use Akeneo\Pim\Enrichment\Component\Product\Factory\Value\MetricValueFactory;
use Akeneo\Pim\Enrichment\Component\Product\Factory\Value\ValueFactory;
use Akeneo\Pim\Enrichment\Component\Product\Model\Metric;
use Akeneo\Pim\Enrichment\Component\Product\Value\MetricValue;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use PhpSpec\ObjectBehavior;

/**
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class MetricValueFactorySpec extends ObjectBehavior
{
    public function let(MetricFactory $metricFactory): void
    {
        $this->beConstructedWith($metricFactory);
    }

    public function it_is_a_read_value_factory(): void
    {
        $this->shouldBeAnInstanceOf(ValueFactory::class);
    }

    public function it_supports_metric_attribute_type(): void
    {
        $this->supportedAttributeType()->shouldReturn(AttributeTypes::METRIC);
    }

    public function it_does_not_support_null(): void
    {
        $this->shouldThrow(InvalidPropertyTypeException::class)->during('createByCheckingData', [
            $this->getAttribute(true, true),
            'ecommerce',
            'fr_FR',
            null
        ]);
    }

    public function it_creates_a_localizable_and_scopable_value(MetricFactory $metricFactory): void
    {
        $metric = new Metric('distance', 'centimeters', 5, 'meters', 0.05, 'cm');
        $metricFactory->createMetric('distance', 'centimeters', 5)->willReturn($metric);
        $attribute = $this->getAttribute(true, true);
        $value = $this->createByCheckingData($attribute, 'ecommerce', 'fr_FR', ['unit' => 'centimeters', 'amount' => 5]);
        $value->shouldBeLike(MetricValue::scopableLocalizableValue('an_attribute', $metric, 'ecommerce', 'fr_FR'));
    }

    public function it_creates_a_localizable_value(MetricFactory $metricFactory): void
    {
        $metric = new Metric('distance', 'centimeters', 5, 'meters', 0.05,'cm');
        $metricFactory->createMetric('distance', 'centimeters', 5)->willReturn($metric);
        $attribute = $this->getAttribute(true, false);
        $value = $this->createByCheckingData($attribute, null, 'fr_FR', ['unit' => 'centimeters', 'amount' => 5]);
        $value->shouldBeLike(MetricValue::localizableValue('an_attribute', $metric, 'fr_FR'));
    }

    public function it_creates_a_scopable_value(MetricFactory $metricFactory): void
    {
        $metric = new Metric('distance', 'centimeters', 5, 'meters', 0.05,'cm');
        $metricFactory->createMetric('distance', 'centimeters', 5)->willReturn($metric);
        $attribute = $this->getAttribute(false, true);
        $value = $this->createByCheckingData($attribute, 'ecommerce', null, ['unit' => 'centimeters', 'amount' => 5]);
        $value->shouldBeLike(MetricValue::scopableValue('an_attribute', $metric, 'ecommerce'));
    }

    public function it_creates_a_non_localizable_and_non_scopable_value(MetricFactory $metricFactory): void
    {
        $metric = new Metric('distance', 'centimeters', 5, 'meters', 0.05, 'cm');
        $metricFactory->createMetric('distance', 'centimeters', 5)->willReturn($metric);
        $attribute = $this->getAttribute(false, false);
        $value = $this->createByCheckingData($attribute, null, null, ['unit' => 'centimeters', 'amount' => 5]);
        $value->shouldBeLike(MetricValue::value('an_attribute', $metric));
    }

    public function it_creates_a_value_without_checking_data(MetricFactory $metricFactory): void
    {
        $metric = new Metric('distance', 'centimeters', 5, 'meters', 0.05, 'cm');
        $metricFactory->createMetric('distance', 'centimeters', 5)->willReturn($metric);
        $attribute = $this->getAttribute(false, false);
        $value = $this->createWithoutCheckingData($attribute, null, null, ['unit' => 'centimeters', 'amount' => 5]);
        $value->shouldBeLike(MetricValue::value('an_attribute', $metric));
    }

    public function it_throws_an_exception_if_provided_data_is_not_an_array(): void
    {
        $attribute = $this->getAttribute(false, false);
        $exception = InvalidPropertyTypeException::arrayExpected(
            'an_attribute',
            MetricValueFactory::class,
            'foobar'
        );

        $this
            ->shouldThrow($exception)
            ->during('createByCheckingData', [$attribute, 'ecommerce', 'en_US', 'foobar']);
    }

    public function it_throws_an_exception_if_provided_data_has_no_amount(): void
    {
        $attribute = $this->getAttribute(false, false);

        $exception = InvalidPropertyTypeException::arrayKeyExpected(
            'an_attribute',
            'amount',
            MetricValueFactory::class,
            ['foo' => 42, 'unit' => 'GRAM']
        );

        $this
            ->shouldThrow($exception)
            ->during('createByCheckingData', [$attribute, 'ecommerce', 'en_US', ['foo' => 42, 'unit' => 'GRAM']]);
    }

    public function it_should_not_throws_an_exception_if_provided_data_has_non_numeric_amount(): void
    {
        $attribute = $this->getAttribute(false, false);

        $this
            ->shouldNotThrow(InvalidPropertyTypeException::class)
            ->during('createByCheckingData', [$attribute, 'ecommerce', 'en_US', ['amount' => 'aa', 'foo' => 42, 'unit' => 'GRAM']]);
    }

    public function it_throws_an_exception_if_provided_data_has_no_unit(): void
    {
        $attribute = $this->getAttribute(false, false);

        $exception = InvalidPropertyTypeException::arrayKeyExpected(
            'an_attribute',
            'unit',
            MetricValueFactory::class,
            ['amount' => 42, 'bar' => 'GRAM']
        );

        $this
            ->shouldThrow($exception)
            ->during('createByCheckingData', [$attribute, 'ecommerce', 'en_US', ['amount' => 42, 'bar' => 'GRAM']]);
    }

    public function it_throws_an_exception_if_provided_data_has_bad_format_unit(): void
    {
        $attribute = $this->getAttribute(false, false);

        $exception = InvalidPropertyTypeException::validArrayStructureExpected(
            'an_attribute',
            sprintf('key "unit" has to be a string, "%s" given', 'array'),
            MetricValueFactory::class,
            ['amount' => 42, 'bar' => 'GRAM', 'unit' => []]
        );

        $this
            ->shouldThrow($exception)
            ->during('createByCheckingData', [$attribute, 'ecommerce', 'en_US', ['amount' => 42, 'bar' => 'GRAM', 'unit' => []]]);
    }

    public function it_throws_an_exception_if_provided_data_has_bad_format_amount(): void
    {
        $attribute = $this->getAttribute(false, false);

        $exception = InvalidPropertyTypeException::decimalExpected(
            'an_attribute',
            MetricValueFactory::class,
            '35999999999999997E-2'
        );

        $this
            ->shouldThrow($exception)
            ->during('createByCheckingData', [$attribute, 'ecommerce', 'en_US', ['amount' => '35999999999999997E-2', 'bar' => 'GRAM', 'unit' => 'GRAM']]);
    }

    private function getAttribute(bool $isLocalizable, bool $isScopable): Attribute
    {
        return new Attribute('an_attribute', AttributeTypes::METRIC, [], $isLocalizable, $isScopable, 'distance', 'METER', false, 'metric', []);
    }
}
