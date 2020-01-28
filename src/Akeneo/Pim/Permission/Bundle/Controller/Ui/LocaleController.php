<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Permission\Bundle\Controller\Ui;

use Akeneo\Channel\Component\Model\Locale;
use Akeneo\Pim\Permission\Bundle\Form\Type\LocaleType;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Templating\EngineInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Locale controller for configuration
 *
 * @author Nicolas Dupont <nicolas@akeneo.com>
 */
class LocaleController
{
    /** @var FormFactoryInterface */
    protected $formFactory;

    /** @var EngineInterface */
    private $templating;

    /** @var TranslatorInterface */
    private $translator;

    public function __construct(FormFactoryInterface $formFactory, EngineInterface $engine, TranslatorInterface $translator)
    {
        $this->formFactory = $formFactory;
        $this->templating = $engine;
        $this->translator = $translator;
    }

    /**
     * Edit a locale
     *
     * @param Locale $locale
     *
     * @AclAncestor("pimee_enrich_locale_edit")
     *
     * @return  Response
     */
    public function editAction(Request $request, Locale $locale): Response
    {
        $form = $this->formFactory->create(LocaleType::class, $locale);
        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $request->getSession()->getFlashBag()->add('success', $this->translator->trans('flash.locale.updated'));

                return new JsonResponse(
                    [
                        'route'  => 'pimee_enrich_locale_edit',
                        'params' => ['id' => $locale->getId()],
                    ]
                );
            }
        }

        return new Response(
            $this->templating->render('AkeneoPimPermissionBundle:Locale:edit.html.twig', ['form' => $form->createView()])
        );
    }
}
