<?php

namespace Akeneo\Pim\Structure\Bundle\Controller\Ui;

use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * Association type controller
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AssociationTypeController
{
    /**
     * Edit an association type
     *
     * @param string $code
     *
     * @Template
     * @AclAncestor("pim_enrich_associationtype_edit")
     *
     * @return array
     */
    public function editAction($code)
    {
        return [
            'code' => $code
        ];
    }
}
