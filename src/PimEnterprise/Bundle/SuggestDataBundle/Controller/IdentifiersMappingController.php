<?php

declare(strict_types=1);

namespace PimEnterprise\Bundle\SuggestDataBundle\Controller;

use PimEnterprise\Component\SuggestData\Application\ManageMapping;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Translation\TranslatorInterface;

class IdentifiersMappingController
{
    private $manageMapping;
    private $translator;

    public function __construct(ManageMapping $manageMapping, TranslatorInterface $translator)
    {
        $this->manageMapping = $manageMapping;
        $this->translator = $translator;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function updateIdentifiersMappingAction(Request $request): JsonResponse
    {
        $identifiersMapping = $request->get('identifiersMapping');

        try {
            $this->manageMapping->updateIdentifierMapping($identifiersMapping);

            return new JsonResponse([
                'successful' => true,
                'message' => $this->translator->trans('pimee_suggest_data.mapping_identifier.success'),
            ]);
        }
        catch (\Exception $e) {
            return new JsonResponse([
                'successful' => false,
                'message' => $this->translator->trans('pimee_suggest_data.mapping_identifier.error'),
            ]);
        }
    }

    /**
     * @return JsonResponse
     */
    public function getIdentifiersMappingAction(): JsonResponse
    {
        return new JsonResponse(
            $this->manageMapping->getIdentifiersMapping()
        );
    }
}
