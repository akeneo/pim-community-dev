<?php

namespace Pim\Bundle\EnrichBundle\Controller;

use PimEnterprise\Bundle\SecurityBundle\Attributes;
use Pim\Bundle\CatalogBundle\Model\LocaleInterface;
use Pim\Bundle\CatalogBundle\Repository\LocaleRepositoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Security\Core\SecurityContextInterface;

/**
 * Locale rest controller
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LocaleRestController
{
    protected $localeRepository;
    protected $securityContext;

    public function __construct(
        LocaleRepositoryInterface $localeRepository,
        SecurityContextInterface $securityContext
    ) {
        $this->localeRepository = $localeRepository;
        $this->securityContext  = $securityContext;
    }

    public function indexAction()
    {
        $locales = $this->localeRepository->getActivatedLocales();

        $normalizedLocales = [];
        foreach ($locales as $locale) {
            if ($this->securityContext->isGranted(Attributes::VIEW_PRODUCTS, $locale)) {
                $normalizedLocales[$locale->getCode()] = $this->normalizeLocale($locale);
            }
        }

        return new JsonResponse($normalizedLocales);
    }

    public function getAction($id)
    {
        $locale = $this->localeRepository->findOneById($id);

        if (!$this->securityContext->isGranted(Attributes::VIEW_PRODUCTS, $locale)) {
            throw new AccessDeniedHttpException('You are not authorized to see this locale');
        }

        return new JsonResponse($this->normalizeLocale($locale));
    }

    protected function normalizeLocale(LocaleInterface $locale)
    {
        return [
            'code'   => $locale->getCode(),
            'rights' => [
                'view' => $this->securityContext->isGranted(Attributes::VIEW_PRODUCTS, $locale),
                'edit' => $this->securityContext->isGranted(Attributes::EDIT_PRODUCTS, $locale)
            ]
        ];
    }
}
