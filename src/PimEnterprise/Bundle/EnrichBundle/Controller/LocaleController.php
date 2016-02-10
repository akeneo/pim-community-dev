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
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;

/**
 * Locale controller for configuration
 *
 * @author Nicolas Dupont <nicolas@akeneo.com>
 */
class LocaleController extends BaseLocaleController
{
    /** @var Request */
    protected $request;

    /** @var FormFactoryInterface */
    protected $formFactory;

    /** @var RouterInterface */
    protected $router;

    /**
     * @param Request              $request
     * @param RouterInterface      $router
     * @param FormFactoryInterface $formFactory
     */
    public function __construct(Request $request, RouterInterface $router, FormFactoryInterface $formFactory)
    {
        $this->request     = $request;
        $this->router      = $router;
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
     * @return array
     */
    public function editAction(Locale $locale)
    {
        $form = $this->formFactory->create('pimee_enrich_locale', $locale);
        if ($this->request->isMethod('POST')) {
            $form->submit($this->request);
            if ($form->isValid()) {
                $this->request->getSession()->getFlashBag()->add('success', new Message('flash.locale.updated'));

                return new RedirectResponse(
                    $this->router->generate('pimee_enrich_locale_edit', ['id' => $locale->getId()])
                );
            }
        }

        return [
            'form' => $form->createView()
        ];
    }
}
