<?php

namespace Pim\Bundle\EnrichBundle\Twig;

/**
 * Twig extension to get attribute icons
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeExtension extends \Twig_Extension
{
    /**
     * @var array
     */
    protected $icons;

    /**
     * Constructor
     *
     * @param array $icons
     */
    public function __construct(array $icons)
    {
        $this->icons = $icons;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return [
            'attribute_icon' => new \Twig_Function_Method($this, 'attributeIcon'),
        ];
    }

    /**
     * Get attribute icon
     *
     * @param string $type
     *
     * @return string
     */
    public function attributeIcon($type)
    {
        return isset($this->icons[$type]) ? $this->icons[$type] : '';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pim_attribute_extension';
    }
}
