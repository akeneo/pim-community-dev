<?php

namespace PimEnterprise\Bundle\EnrichBundle\Controller;

use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Pim\Bundle\EnrichBundle\Controller\LocaleController as BaseLocaleController;
use Pim\Bundle\CatalogBundle\Entity\Locale;

/**
 * Locale controller for configuration
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
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
     * @return array
     */
    public function editAction(Locale $locale)
    {
        $form = $this->createForm('pimee_enrich_locale', $locale);
        if ($this->request->isMethod('POST')) {
            $form->submit($this->request);
            if ($form->isValid()) {
                $this->addFlash('success', 'flash.locale.updated');
            }
        }

        return array(
            'form' => $form->createView()
        );
    }
}
