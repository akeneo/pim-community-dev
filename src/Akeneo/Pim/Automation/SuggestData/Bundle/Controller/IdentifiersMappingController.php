<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\SuggestData\Bundle\Controller;

use Akeneo\Pim\Automation\SuggestData\Component\Application\ManageIdentifiersMapping;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class IdentifiersMappingController
{
    /** @var ManageIdentifiersMapping */
    private $manageIdentifiersMapping;

    /** @var TranslatorInterface */
    private $translator;

    /**
     * @param ManageIdentifiersMapping $manageIdentifiersMapping
     * @param TranslatorInterface      $translator
     */
    public function __construct(ManageIdentifiersMapping $manageIdentifiersMapping, TranslatorInterface $translator)
    {
        $this->manageIdentifiersMapping = $manageIdentifiersMapping;
        $this->translator = $translator;
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function updateIdentifiersMappingAction(Request $request): JsonResponse
    {
        $identifiersMapping = $request->get('identifiersMapping');

        try {
            $this->manageIdentifiersMapping->updateIdentifierMapping($identifiersMapping);

            return new JsonResponse([
                'status' => 'success',
                'message' => $this->translator->trans('akeneo_suggest_data.mapping_identifier.success'),
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'status' => 'error',
                'message' => $this->translator->trans('akeneo_suggest_data.mapping_identifier.error'),
            ]);
        }
    }

    /**
     * @return JsonResponse
     */
    public function getIdentifiersMappingAction(): JsonResponse
    {
        return new JsonResponse(
            $this->manageIdentifiersMapping->getIdentifiersMapping()
        );
    }
}
