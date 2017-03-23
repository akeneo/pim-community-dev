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
use Pim\Bundle\EnrichBundle\Flash\Message;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Locale controller for configuration
 *
 * @author Nicolas Dupont <nicolas@akeneo.com>
 */
class LocaleController extends BaseLocaleController
{
    /** @var FormFactoryInterface */
    protected $formFactory;

    /**
     * @param FormFactoryInterface $formFactory
     */
    public function __construct(FormFactoryInterface $formFactory)
    {
        $this->formFactory = $formFactory;
    }

    /**
     * Edit a locale
     *
     * @param Locale $locale
     *
     * @Template
     * @AclAncestor("pimee_enrich_locale_edit")
     *
     * @return JsonResponse|array
     */
    public function editAction(Request $request, Locale $locale)
    {
        $form = $this->formFactory->create('pimee_enrich_locale', $locale);
        if ($request->isMethod('POST')) {
            $form->submit($request);
            if ($form->isValid()) {
                $request->getSession()->getFlashBag()->add('success', new Message('flash.locale.updated'));

                return new JsonResponse(
                    [
                        'route'  => 'pimee_enrich_locale_edit',
                        'params' => ['id' => $locale->getId()],
                    ]
                );
            }
        }

        return [
            'form' => $form->createView()
        ];
    }
}
