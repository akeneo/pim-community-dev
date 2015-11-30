<?php

namespace Pim\Component\Localization\Presenter;

use Pim\Component\Localization\Localizer\NumberLocalizer;

/**
 * Metric presenter, able to render metric data readable for a human
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MetricPresenter implements PresenterInterface
{
    /** @var NumberLocalizer */
    protected $numberLocalizer;

    /** @var string[] */
    protected $attributeTypes;

    /**
     * @param NumberLocalizer $numberLocalizer
     * @param string[]        $attributeTypes
     */
    public function __construct(NumberLocalizer $numberLocalizer, array $attributeTypes)
    {
        $this->numberLocalizer = $numberLocalizer;
        $this->attributeTypes  = $attributeTypes;
    }

    /**
     * {@inheritdoc}
     */
    public function present($value, array $options = [])
    {
        $amount = $this->numberLocalizer->localize($value['data'], $options);

        return sprintf('%s %s', $amount, $value['unit']);
    }

    /**
     * {@inheritdoc}
     */
    public function supports($attributeType)
    {
        return in_array($attributeType, $this->attributeTypes);
    }
}
