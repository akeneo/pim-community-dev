<?php

namespace PimEnterprise\Bundle\EnrichBundle\Controller;

use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;

use Pim\Bundle\EnrichBundle\Controller\SequentialEditController as BaseSequentialEditController;

/**
 * Sequential edit action controller for products
 *
 * @author    Rémy Bétus <remy.betus@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SequentialEditController extends BaseSequentialEditController
{
    /**
     * Action for product sequential edition
     *
     * @AclAncestor("pim_enrich_product_edit_attributes")
     *
     * @return RedirectResponse
     */
    public function sequentialEditAction()
    {
        $sequentialEdit = $this->seqEditManager->createEntity(
            $this->getObjects(),
            $this->userContext->getUser()
        );

        if ($this->seqEditManager->findByUser($this->getUser())) {
            return $this->redirectToRoute(
                'pim_enrich_product_index',
                ['dataLocale' => $this->request->get('dataLocale')]
            );
        }

        $this->seqEditManager->save($sequentialEdit);

        return $this->redirectToRoute(
            'pimee_enrich_product_dispatch',
            array(
                'dataLocale' => $this->request->get('dataLocale'),
                'id'         => current($sequentialEdit->getObjectSet())
            )
        );
    }
}
