<?php

namespace Pim\Bundle\EnrichBundle\Controller;

use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Symfony\Component\HttpFoundation\JsonResponse;
use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Attribute option controller
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeOptionController
{
    protected $serializer;

    /**
     * Constructor
     *
     * @param SerializerInterface $serializer
     */
    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * Get all options of an attribute
     *
     * @param AbstractAttribute $attribute
     *
     * @return AttributeOption[]
     *
     * @ParamConverter("attribute", class="PimCatalogBundle:Attribute", options={"id" = "attribute_id"})
     * @AclAncestor("pim_enrich_attribute_edit")
     */
    public function indexAction(AbstractAttribute $attribute)
    {
        $options = $this->serializer->normalize($attribute->getOptions(), 'array', ['onlyOptions' => true]);

        return new JsonResponse($options);
    }
}
