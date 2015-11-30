<?php

namespace Pim\Component\Localization\Presenter;

use Pim\Component\Localization\Localizer\NumberLocalizer;

/**
 * Number presenter, able to render numbers readable for a human
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class NumberPresenter implements PresenterInterface
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
        return $this->numberLocalizer->localize($value, $options);
    }

    /**
     * {@inheritdoc}
     */
    public function supports($attributeType)
    {
        return in_array($attributeType, $this->attributeTypes);
    }
}
