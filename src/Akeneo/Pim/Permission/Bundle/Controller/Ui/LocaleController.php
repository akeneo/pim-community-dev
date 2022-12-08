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

use Akeneo\Channel\Infrastructure\Component\Repository\LocaleRepositoryInterface;
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
    public function __construct(
        private readonly FormFactoryInterface $formFactory,
        private readonly Environment $twig,
        private readonly TranslatorInterface $translator,
        private readonly FeatureFlag $dictionaryFeatureFlag,
        private readonly SupportedLocaleValidator $supportedLocaleValidator,
        private readonly LocaleRepositoryInterface $localeRepository,
    ) {
    }

    /**
     * Edit a locale
     *
     * @AclAncestor("pimee_enrich_locale_edit")
     */
    public function editAction(Request $request, int $id): Response
    {
        $locale = $this->localeRepository->find($id);
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
            $this->twig->render('@AkeneoPimPermission/Locale/edit.html.twig', [
                'form' => $form->createView(),
                'dictionaryEnabled' => $dictionaryEnabled,
            ])
        );
    }
}
