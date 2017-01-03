<?php

namespace Pim\Bundle\EnrichBundle\Twig;

use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;

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

    /** @var IdentifiableObjectRepositoryInterface */
    protected $attributeRepository;

    /**
     * Constructor
     *
     * @param array $icons
     */
    public function __construct(array $icons, IdentifiableObjectRepositoryInterface $attributeRepository)
    {
        $this->icons = $icons;
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return [
            'attribute_icon' => new \Twig_Function_Method($this, 'attributeIcon'),
            new \Twig_SimpleFunction(
                'get_attribute_label_from_code',
                [$this, 'getAttributeLabelFromCode'],
                ['is_safe' => ['html']]
            ),
        ];
    }

    /**
     * @param string $code
     *
     * @return string
     */
    public function getAttributeLabelFromCode($code)
    {
        if (null !== $attribute = $this->attributeRepository->findOneByIdentifier($code)) {
            return (string) $attribute;
        }

        return $code;
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
