<?php

namespace Pim\Bundle\UIBundle\Form\Type;

use Pim\Bundle\UIBundle\Form\Transformer\AjaxReferenceDataTransformerFactory;
use Pim\Bundle\UserBundle\Context\UserContext;
use Symfony\Component\Routing\RouterInterface;

/**
 * Ajax reference data type
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AjaxReferenceDataType extends AjaxEntityType
{
    /**
     * Constructor
     *
     * @param RouterInterface                     $router
     * @param AjaxReferenceDataTransformerFactory $transformerFactory
     * @param UserContext                         $userContext
     */
    public function __construct(
        RouterInterface $router,
        AjaxReferenceDataTransformerFactory $transformerFactory,
        UserContext $userContext
    ) {
        $this->router             = $router;
        $this->transformerFactory = $transformerFactory;
        $this->userContext        = $userContext;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pim_ajax_reference_data';
    }
}
