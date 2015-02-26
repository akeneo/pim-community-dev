<?php

namespace Pim\Bundle\CatalogBundle\Updater\Setter;

use Pim\Bundle\CatalogBundle\Model\AttributeInterface;

/**
 * Abstract field setter
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class AbstractFieldSetter implements FieldSetterInterface
{
    /** @var array */
    protected $supportedFields = [];

    /**
     * {@inheritdoc}
     */
    public function supports(AttributeInterface $attribute)
    {
        return false;
    }

    public function setValue(array $products, AttributeInterface $attribute, $data, $locale = null, $scope = null)
    {
        throw new \Exception('This method is not supported for field setters');

    }

    /**
     * {@inheritdoc}
     */
    public function supportsField($field)
    {
        return in_array($field, $this->supportedFields);
    }
}
