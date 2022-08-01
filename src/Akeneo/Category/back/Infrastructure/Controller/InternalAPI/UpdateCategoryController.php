<?php

declare(strict_types=1);

namespace Akeneo\Category\back\Infrastructure\Controller\InternalAPI;

use Akeneo\Category\Api\Command\CommandMessageBus;
use Akeneo\Category\Api\Command\Exceptions\ViolationsException;
use Akeneo\Category\Api\Command\UpsertCategoryCommand;
use Akeneo\Category\Application\Converter\ConverterInterface;
use Akeneo\Category\Application\Converter\StandardFormatToUserIntentsStub;
use Akeneo\Category\Application\Filter\CategoryEditUserIntentFilter;
use Akeneo\Category\Application\Query\FindCategoryByIdentifier;
use Akeneo\Category\Application\Filter\CategoryEditACLFilter;
use Akeneo\Platform\Bundle\FeatureFlagBundle\Configuration\EnvVarFeatureFlag;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UpdateCategoryController
{
    public function __construct(
        private CommandMessageBus $categoryCommandBus,
        private SecurityFacade $securityFacade,
        private ConverterInterface $internalApiToStandardConverter,
        private CategoryEditACLFilter $ACLFilter,
        private StandardFormatToUserIntentsStub $standardFormatToUserIntents,
        private CategoryEditUserIntentFilter $categoryUserIntentFilter,
        private EnvVarFeatureFlag $enrichedCategoryFeature,
        private FindCategoryByIdentifier $findCategoryByIdentifier,
        private array $rawConfiguration,
    ) {
    }

    public function __invoke(Request $request, int $id): Response
    {
        if ($this->securityFacade->isGranted($this->buildAclName('category_edit')) === false) {
            throw new AccessDeniedException();
        }
        if (!$this->enrichedCategoryFeature->isEnabled()) {
            throw new \RuntimeException('The feature is not enabled');
        }

        if (($this->findCategoryByIdentifier)($id) === null) {
            return new JsonResponse(['message' => 'Category not found'], Response::HTTP_NOT_FOUND);
        }
        $data = [];
        $formattedData = $this->internalApiToStandardConverter->convert($data);
        $filteredData = $this->ACLFilter->filterCollection($formattedData, 'category', []);
        $userIntents = $this->standardFormatToUserIntents->convert($filteredData);
        $filteredUserIntents = $this->categoryUserIntentFilter->filterCollection($userIntents);

        try {
            $command = UpsertCategoryCommand::create(
                $id,
                $filteredUserIntents
            );
            $this->categoryCommandBus->dispatch($command);
        } catch (ViolationsException $e) {
            //Todo: Handle violations exceptions when all stubbed services have been replaced by real ones
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
        $category = ($this->findCategoryByIdentifier)($id);
        $normalizedCategory = $category?->normalize();

        return new JsonResponse($normalizedCategory, Response::HTTP_OK);
    }

    private function buildAclName(string $name): string
    {
        return $this->rawConfiguration['acl'] . '_' . $name;
    }
}
