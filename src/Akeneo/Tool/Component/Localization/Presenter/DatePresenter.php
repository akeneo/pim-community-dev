<?php

namespace Akeneo\Tool\Component\Localization\Presenter;

use Akeneo\Tool\Component\Localization\Factory\DateFactory;

/**
 * Date presenter, able to render date readable for a human
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DatePresenter implements PresenterInterface
{
    /** @var DateFactory */
    protected $dateFactory;

    /** @var string[] */
    protected $attributeTypes;

    /**
     * @param DateFactory $dateFactory
     * @param string[]    $attributeTypes
     */
    public function __construct(DateFactory $dateFactory, array $attributeTypes)
    {
        $this->dateFactory = $dateFactory;
        $this->attributeTypes = $attributeTypes;
    }

    /**
     * {@inheritdoc}
     */
    public function present($value, array $options = [])
    {
        if (null === $value || '' === $value) {
            return $value;
        }

        if (is_array($value)) {
            return $this->presentArray($value, $options);
        }

        if (!($value instanceof \DateTime)) {
            $value = new \DateTime($value);
        }

        $formatter = $this->dateFactory->create($options);

        return $formatter->format($value);
    }

    /**
     * {@inheritdoc}
     */
    public function supports($attributeType)
    {
        return in_array($attributeType, $this->attributeTypes);
    }

    /**
     * Presents an array of values to be readable
     *
     * @param array $values  The original values
     * @param array $options The options for presentation
     *
     * @return string
     */
    protected function presentArray($values, $options)
    {
        $formattedValues = [];
        foreach ($values as $value) {
            $formattedValues[] = $this->present($value, $options);
        }

        return implode(', ', $formattedValues);
    }
}
