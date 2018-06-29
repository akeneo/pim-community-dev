<?php

declare(strict_types=1);

namespace PimEnterprise\Bundle\SuggestDataBundle\Controller;

use PimEnterprise\Component\SuggestData\Application\ManageMapping;
use PimEnterprise\Component\SuggestData\Repository\IdentifiersMappingRepositoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Translation\TranslatorInterface;

class IdentifiersMappingController
{
    private $manageMapping;
    private $translator;
    private $identifiersMappingRepository;

    public function __construct(ManageMapping $manageMapping, TranslatorInterface $translator, IdentifiersMappingRepositoryInterface $identifiersMappingRepository)
    {
        $this->manageMapping = $manageMapping;
        $this->translator = $translator;
        $this->identifiersMappingRepository = $identifiersMappingRepository;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function updateIdentifiersMappingAction(Request $request)
    {
        $identifiersMapping = $request->get('identifiersMapping');

        $isUpdated = $this->manageMapping->updateIdentifierMapping($identifiersMapping);

        if(true === $isUpdated) {
            return new JsonResponse([
                'successful' => true,
                'message' => $this->translator->trans('pimee_suggest_data.mapping_identifier.success'),
            ]);
        }
        else {
            return new JsonResponse([
                'successful' => false,
                'message' => $this->translator->trans('pimee_suggest_data.mapping_identifier.error'),
            ]);
        }

    }

    /**
     * @return JsonResponse
     */
    public function getIdentifiersMappingAction() {
        $identifiersMapping = $this->identifiersMappingRepository->findAll();

        return new JsonResponse($identifiersMapping->getIdentifiers());
    }
}
