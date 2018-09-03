<?php

namespace Akeneo\Tool\Component\Localization\Presenter;

use Akeneo\Tool\Component\Localization\Factory\NumberFactory;

/**
 * Number presenter, able to render numbers readable for a human
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class NumberPresenter implements PresenterInterface
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
        $this->numberFactory = $numberFactory;
        $this->attributeTypes = $attributeTypes;
    }

    /**
     * {@inheritdoc}
     */
    public function present($value, array $options = [])
    {
        if (!is_numeric($value)) {
            return $value;
        }

        $numberFormatter = $this->numberFactory->create($options);

        if (floor($value) != $value) {
            $numberFormatter->setAttribute(\NumberFormatter::MIN_FRACTION_DIGITS, 2);
            $numberFormatter->setAttribute(\NumberFormatter::MAX_FRACTION_DIGITS, 4);
        }

        return $numberFormatter->format($value);
    }

    /**
     * {@inheritdoc}
     */
    public function supports($attributeType)
    {
        return in_array($attributeType, $this->attributeTypes);
    }
}
