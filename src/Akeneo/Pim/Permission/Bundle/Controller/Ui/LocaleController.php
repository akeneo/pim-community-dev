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
use Akeneo\Pim\Automation\DataQualityInsights\Application\Spellcheck\SupportedLocaleValidator;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Permission\Bundle\Form\Type\LocaleType;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;
use Webmozart\Assert\Assert;

/**
 * Locale controller for configuration
 *
 * @author Nicolas Dupont <nicolas@akeneo.com>
 */
class LocaleController
{
    protected FormFactoryInterface $formFactory;

    private Environment $templating;

    private TranslatorInterface $translator;

    private FeatureFlag $dictionaryFeatureFlag;

    private SupportedLocaleValidator $supportedLocaleValidator;

    public function __construct(
        FormFactoryInterface $formFactory,
        Environment $engine,
        TranslatorInterface $translator,
        FeatureFlag $dictionaryFeatureFlag,
        SupportedLocaleValidator $supportedLocaleValidator
    ) {
        $this->formFactory = $formFactory;
        $this->templating = $engine;
        $this->translator = $translator;
        $this->dictionaryFeatureFlag = $dictionaryFeatureFlag;
        $this->supportedLocaleValidator = $supportedLocaleValidator;
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
                $session = $request->getSession();
                Assert::isInstanceOf($session, Session::class);
                $session->getFlashBag()->add('success', $this->translator->trans('flash.locale.updated'));

                return new JsonResponse(
                    [
                        'route'  => 'pimee_enrich_locale_edit',
                        'params' => ['id' => $locale->getId()],
                    ]
                );
            }
        }

        $dictionaryEnabled = (
            $this->dictionaryFeatureFlag->isEnabled() &&
            $this->supportedLocaleValidator->isSupported(new LocaleCode($locale))
        );

        return new Response(
            $this->templating->render('AkeneoPimPermissionBundle:Locale:edit.html.twig', [
                'form' => $form->createView(),
                'dictionaryEnabled' => $dictionaryEnabled,
            ])
        );
    }
}
