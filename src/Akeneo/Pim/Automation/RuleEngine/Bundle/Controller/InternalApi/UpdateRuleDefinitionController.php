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
use Akeneo\Pim\Automation\RuleEngine\Component\Updater\RuleDefinitionUpdaterInterface;
use Akeneo\Tool\Bundle\RuleEngineBundle\Doctrine\Common\Saver\RuleDefinitionSaver;
use Akeneo\Tool\Bundle\RuleEngineBundle\Normalizer\RuleDefinitionNormalizer;
use Akeneo\Tool\Bundle\RuleEngineBundle\Repository\RuleDefinitionRepositoryInterface;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UpdateRuleDefinitionController
{
    /** @var RuleDefinitionRepositoryInterface */
    private $ruleDefinitionRepository;

    /** @var RuleDefinitionUpdaterInterface */
    private $ruleDefinitionUpdater;

    /** @var RuleDefinitionSaver */
    private $ruleDefinitionSaver;

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
        RuleDefinitionUpdaterInterface $ruleDefinitionUpdater,
        RuleDefinitionSaver $ruleDefinitionSaver,
        RuleDefinitionNormalizer $ruleDefinitionNormalizer,
        NormalizerInterface $normalizer,
        ValidatorInterface $validator,
        SecurityFacade $securityFacade
    ) {
        $this->ruleDefinitionRepository = $ruleDefinitionRepository;
        $this->ruleDefinitionUpdater = $ruleDefinitionUpdater;
        $this->ruleDefinitionSaver = $ruleDefinitionSaver;
        $this->ruleDefinitionNormalizer = $ruleDefinitionNormalizer;
        $this->normalizer = $normalizer;
        $this->validator = $validator;
        $this->securityFacade = $securityFacade;
    }

    public function __invoke(string $ruleDefinitionCode, Request $request): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        if (!$this->securityFacade->isGranted('pimee_catalog_rule_rule_edit_permissions')) {
            throw new AccessDeniedException();
        }

        $ruleDefinition = $this->ruleDefinitionRepository->findOneByIdentifier($ruleDefinitionCode);
        if (null === $ruleDefinition) {
            throw new NotFoundHttpException(sprintf('The "%s" rule definition was not found', $ruleDefinitionCode));
        }
        $data = json_decode($request->getContent(), true);
        $data['code'] = $ruleDefinitionCode;
        $command = new CreateOrUpdateRuleCommand($data);

        $violations = $this->validator->validate($command, null, ['Default', 'update']);
        if ($violations->count()) {
            $errors = $this->normalizer->normalize($violations, 'internal_api');

            return new JsonResponse($errors, Response::HTTP_BAD_REQUEST);
        }

        $this->ruleDefinitionUpdater->update($ruleDefinition, $command->toArray(true));
        $this->ruleDefinitionSaver->save($ruleDefinition);

        return new JsonResponse($this->ruleDefinitionNormalizer->normalize($ruleDefinition));
    }
}
