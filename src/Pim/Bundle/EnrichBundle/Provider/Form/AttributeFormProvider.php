<?php

namespace Pim\Bundle\EnrichBundle\Provider\Form;

use Pim\Component\Catalog\Model\AttributeInterface;

/**
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class AttributeFormProvider implements FormProviderInterface
{
    /** @var array */
    protected $formConfig;

    /**
     * @param array $formConfig
     */
    public function __construct($formConfig)
    {
        $this->formConfig = $formConfig;
    }

    /**
     * {@inheritdoc}
     */
    public function getForm($attribute)
    {
        return $this->formConfig[$attribute->getType()];
    }

    /**
     * {@inheritdoc}
     */
    public function supports($element)
    {
        return $element instanceof AttributeInterface && isset($this->formConfig[$element->getType()]);
    }
}
