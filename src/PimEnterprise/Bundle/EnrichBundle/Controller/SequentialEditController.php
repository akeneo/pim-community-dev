<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\EnrichBundle\Controller;

use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Pim\Bundle\EnrichBundle\Controller\SequentialEditController as BaseSequentialEditController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Sequential edit action controller for products
 *
 * @author Rémy Bétus <remy.betus@akeneo.com>
 */
class SequentialEditController extends BaseSequentialEditController
{
    /**
     * {@inheritdoc}
     *
     * @AclAncestor("pim_enrich_product_edit_attributes")
     */
    public function sequentialEditAction(Request $request)
    {
        $sequentialEdit = $this->seqEditManager->createEntity(
            $this->getObjects($request),
            $this->userContext->getUser()
        );

        if ($this->seqEditManager->findByUser($this->userContext->getUser())) {
            return new RedirectResponse(
                $this->router->generate(
                    'pim_enrich_product_index',
                    ['dataLocale' => $request->get('dataLocale')]
                )
            );
        }
        $this->seqEditManager->save($sequentialEdit);

        return new RedirectResponse(
            $this->router->generate(
                'pim_enrich_product_edit',
                [
                    'dataLocale' => $request->get('dataLocale'),
                    'id' => current($sequentialEdit->getObjectSet())
                ]
            )
        );
    }
}
