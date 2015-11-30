<?php

namespace Pim\Component\Localization\Presenter;

use Pim\Component\Localization\Factory\NumberFactory;

/**
 * Metric presenter, able to render metric data readable for a human
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MetricPresenter implements PresenterInterface
{
    /** @var NumberFactory */
    protected $numberFactory;

    /** @var string[] */
    protected $attributeTypes;

    /**
     * @param NumberFactory $numberFactory
     * @param string[]      $attributeTypes
     */
    public function __construct(NumberFactory $numberFactory, array $attributeTypes)
    {
        $this->numberFactory  = $numberFactory;
        $this->attributeTypes = $attributeTypes;
    }

    /**
     * {@inheritdoc}
     */
    public function present($value, array $options = [])
    {
        $numberFormatter = $this->numberFactory->create($options);
        if (isset($options['disable_grouping_separator']) && true === $options['disable_grouping_separator']) {
            $numberFormatter->setSymbol(\NumberFormatter::GROUPING_SEPARATOR_SYMBOL, '');
        }
        $amount = $numberFormatter->format($value['data']);

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
