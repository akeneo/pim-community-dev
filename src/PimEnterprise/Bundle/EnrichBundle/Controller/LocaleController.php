<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\EnrichBundle\Controller;

use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Pim\Bundle\CatalogBundle\Entity\Locale;
use Pim\Bundle\EnrichBundle\Controller\LocaleController as BaseLocaleController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * Locale controller for configuration
 *
 * @author Nicolas Dupont <nicolas@akeneo.com>
 */
class LocaleController extends BaseLocaleController
{
    /**
     * Edit a locale
     *
     * @param Locale $locale
     *
     * @Template
     * @AclAncestor("pimee_enrich_locale_edit")
     *
     * @return array
     */
    public function editAction(Locale $locale)
    {
        $form = $this->createForm('pimee_enrich_locale', $locale);
        if ($this->request->isMethod('POST')) {
            $form->submit($this->request);
            if ($form->isValid()) {
                $this->addFlash('success', 'flash.locale.updated');

                return $this->redirectToRoute('pimee_enrich_locale_edit', ['id' => $locale->getId()]);
            }
        }

        return [
            'form' => $form->createView()
        ];
    }
}
