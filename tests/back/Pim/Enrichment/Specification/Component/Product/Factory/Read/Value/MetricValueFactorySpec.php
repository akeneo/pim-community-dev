<?php
declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Factory\Read\Value;

use Akeneo\Pim\Enrichment\Component\Product\Factory\MetricFactory;
use Akeneo\Pim\Enrichment\Component\Product\Factory\Read\Value\ReadValueFactory;
use Akeneo\Pim\Enrichment\Component\Product\Model\Metric;
use Akeneo\Pim\Enrichment\Component\Product\Value\ScalarValue;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use PhpSpec\ObjectBehavior;

/**
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class MetricValueFactorySpec extends ObjectBehavior
{
    public function let(MetricFactory $metricFactory)
    {
        $this->beConstructedWith($metricFactory);
    }

    public function it_is_a_read_value_factory()
    {
        $this->shouldBeAnInstanceOf(ReadValueFactory::class);
    }

    public function it_supports_metric_attribute_type()
    {
        $this->supportedAttributeType()->shouldReturn(AttributeTypes::METRIC);
    }

    public function it_creates_a_localizable_and_scopable_value(MetricFactory $metricFactory)
    {
        $metric = new Metric('distance', 'centimeters', 5, 'meters', 0.05);
        $metricFactory->createMetric('distance', 'centimeters', 5)->willReturn($metric);
        $attribute = $this->getAttribute(true, true);
        /** @var ScalarValue $value */
        $value = $this->create($attribute, 'ecommerce', 'fr_FR', ['unit' => 'centimeters', 'amount' => 5]);
        $value->isLocalizable()->shouldBe(true);
        $value->isScopable()->shouldBe(true);
        $value->getData()->shouldBe($metric);
    }

    public function it_creates_a_localizable_value(MetricFactory $metricFactory)
    {
        $metric = new Metric('distance', 'centimeters', 5, 'meters', 0.05);
        $metricFactory->createMetric('distance', 'centimeters', 5)->willReturn($metric);
        $attribute = $this->getAttribute(true, false);
        /** @var ScalarValue $value */
        $value = $this->create($attribute, null, 'fr_FR', ['unit' => 'centimeters', 'amount' => 5]);
        $value->isLocalizable()->shouldBe(true);
        $value->isScopable()->shouldBe(false);
        $value->getData()->shouldBe($metric);
    }

    public function it_creates_a_scopable_value(MetricFactory $metricFactory)
    {
        $metric = new Metric('distance', 'centimeters', 5, 'meters', 0.05);
        $metricFactory->createMetric('distance', 'centimeters', 5)->willReturn($metric);
        $attribute = $this->getAttribute(false, true);
        /** @var ScalarValue $value */
        $value = $this->create($attribute, 'ecommerce', null, ['unit' => 'centimeters', 'amount' => 5]);
        $value->isLocalizable()->shouldBe(false);
        $value->isScopable()->shouldBe(true);
        $value->getData()->shouldBe($metric);
    }

    public function it_creates_a_non_localizable_and_non_scopable_value(MetricFactory $metricFactory)
    {
        $metric = new Metric('distance', 'centimeters', 5, 'meters', 0.05);
        $metricFactory->createMetric('distance', 'centimeters', 5)->willReturn($metric);
        $attribute = $this->getAttribute(false, false);
        /** @var ScalarValue $value */
        $value = $this->create($attribute, null, null, ['unit' => 'centimeters', 'amount' => 5]);
        $value->isLocalizable()->shouldBe(false);
        $value->isScopable()->shouldBe(false);
        $value->getData()->shouldBe($metric);
    }

    private function getAttribute(bool $isLocalizable, bool $isScopable): Attribute
    {
        return new Attribute('an_attribute', AttributeTypes::METRIC, [], $isLocalizable, $isScopable, 'distance', false);
    }
}
