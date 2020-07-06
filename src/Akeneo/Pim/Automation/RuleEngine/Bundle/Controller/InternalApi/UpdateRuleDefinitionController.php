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
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
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

    public function __construct(
        RuleDefinitionRepositoryInterface $ruleDefinitionRepository,
        RuleDefinitionUpdaterInterface $ruleDefinitionUpdater,
        RuleDefinitionSaver $ruleDefinitionSaver,
        RuleDefinitionNormalizer $ruleDefinitionNormalizer,
        NormalizerInterface $normalizer,
        ValidatorInterface $validator
    ) {
        $this->ruleDefinitionRepository = $ruleDefinitionRepository;
        $this->ruleDefinitionUpdater = $ruleDefinitionUpdater;
        $this->ruleDefinitionSaver = $ruleDefinitionSaver;
        $this->ruleDefinitionNormalizer = $ruleDefinitionNormalizer;
        $this->normalizer = $normalizer;
        $this->validator = $validator;
    }

    public function __invoke(string $ruleDefinitionCode, Request $request): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $ruleDefinition = $this->ruleDefinitionRepository->findOneByIdentifier($ruleDefinitionCode);
        if (null === $ruleDefinition) {
            throw new NotFoundHttpException(sprintf('The "%s" rule definition is not found', $ruleDefinitionCode));
        }
        $content = json_decode($request->getContent(), true);
        $content['type'] = 'product';
        $content['code'] = $ruleDefinitionCode;

        $data = $content;
        $data['conditions'] = $data['content']['conditions'] ?? null;
        $data['actions'] = $data['content']['actions'] ?? null;
        $command = new CreateOrUpdateRuleCommand($data);
        $violations = $this->validator->validate($command, null, ['Default', 'update']);
        if ($violations->count()) {
            $errors = $this->normalizer->normalize($violations, 'internal_api');

            return new JsonResponse($errors, Response::HTTP_BAD_REQUEST);
        }

        $this->ruleDefinitionUpdater->update($ruleDefinition, $content);
        $this->ruleDefinitionSaver->save($ruleDefinition);

        return new JsonResponse($this->ruleDefinitionNormalizer->normalize($ruleDefinition));
    }
}
