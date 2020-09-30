<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\RuleEngine\Bundle\Controller\InternalApi;

use Akeneo\Pim\Automation\RuleEngine\Component\Command\CreateOrUpdateRuleCommand;
use Akeneo\Pim\Automation\RuleEngine\Component\Command\CreateOrUpdateRuleDefinitionHandler;
use Akeneo\Tool\Bundle\RuleEngineBundle\Normalizer\RuleDefinitionNormalizer;
use Akeneo\Tool\Bundle\RuleEngineBundle\Repository\RuleDefinitionRepositoryInterface;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class DuplicateRuleDefinitionController
{
    /** @var RuleDefinitionRepositoryInterface */
    private $ruleDefinitionRepository;

    /** @var CreateOrUpdateRuleDefinitionHandler */
    private $createRuleDefinitionHandler;

    /** @var RuleDefinitionNormalizer */
    private $ruleDefinitionNormalizer;

    /** @var NormalizerInterface */
    private $normalizer;

    /** @var ValidatorInterface */
    private $validator;

    /** @var SecurityFacade */
    private $securityFacade;

    public function __construct(
        RuleDefinitionRepositoryInterface $ruleDefinitionRepository,
        CreateOrUpdateRuleDefinitionHandler $createRuleDefinitionHandler,
        RuleDefinitionNormalizer $ruleDefinitionNormalizer,
        NormalizerInterface $normalizer,
        ValidatorInterface $validator,
        SecurityFacade $securityFacade
    ) {
        $this->ruleDefinitionRepository = $ruleDefinitionRepository;
        $this->createRuleDefinitionHandler = $createRuleDefinitionHandler;
        $this->ruleDefinitionNormalizer = $ruleDefinitionNormalizer;
        $this->normalizer = $normalizer;
        $this->validator = $validator;
        $this->securityFacade = $securityFacade;
    }

    public function __invoke(Request $request, string $originalRuleCode): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        if (!$this->securityFacade->isGranted('pimee_catalog_rule_rule_edit_permissions')) {
            throw new AccessDeniedException();
        }

        $newCode = $request->get('code', null);
        $newLabels = $request->get('labels', []);

        $originalRuleDefinition = $this->ruleDefinitionRepository->findOneByIdentifier($originalRuleCode);
        if (null === $originalRuleDefinition) {
            return new JsonResponse(null, Response::HTTP_NOT_FOUND);
        }

        $createCommand = new CreateOrUpdateRuleCommand(
            array_replace(
                $this->ruleDefinitionNormalizer->normalize($originalRuleDefinition),
                [
                    'code' => $newCode,
                    'labels' => $newLabels,
                    'enabled' => false,
                ]
            )
        );

        $violations = $this->validator->validate($createCommand, null, ['Default', 'create']);
        if ($violations->count()) {
            $errors = $this->normalizer->normalize($violations, 'internal_api');

            return new JsonResponse($errors, Response::HTTP_BAD_REQUEST);
        }
        ($this->createRuleDefinitionHandler)($createCommand);

        return new JsonResponse(
            $this->ruleDefinitionNormalizer->normalize(
                $this->ruleDefinitionRepository->findOneByIdentifier($newCode)
            )
        );
    }
}
