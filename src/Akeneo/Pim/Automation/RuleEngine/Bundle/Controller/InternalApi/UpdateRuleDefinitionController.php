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

use Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi\ViolationNormalizer;
use Akeneo\Tool\Bundle\RuleEngineBundle\Doctrine\Common\Saver\RuleDefinitionSaver;
use Akeneo\Tool\Bundle\RuleEngineBundle\Repository\RuleDefinitionRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UpdateRuleDefinitionController
{
    /** @var RuleDefinitionRepositoryInterface */
    private $ruleDefinitionRepository;

    /** @var ObjectUpdaterInterface */
    private $ruleDefinitionUpdater;

    /** @var RuleDefinitionSaver */
    private $ruleDefinitionSaver;

    /** @var NormalizerInterface */
    private $normalizer;

    /** @var ValidatorInterface */
    private $validator;

    /** @var ViolationNormalizer */
    private $violationNormalizer;

    public function __construct(
        RuleDefinitionRepositoryInterface $ruleDefinitionRepository,
        ObjectUpdaterInterface $ruleDefinitionUpdater,
        RuleDefinitionSaver $ruleDefinitionSaver,
        NormalizerInterface $normalizer,
        ValidatorInterface $validator,
        ViolationNormalizer $violationNormalizer
    ) {
        $this->ruleDefinitionRepository = $ruleDefinitionRepository;
        $this->ruleDefinitionUpdater = $ruleDefinitionUpdater;
        $this->ruleDefinitionSaver = $ruleDefinitionSaver;
        $this->normalizer = $normalizer;
        $this->validator = $validator;
        $this->violationNormalizer = $violationNormalizer;
    }

    public function __invoke(string $ruleDefinitionCode, Request $request)
    {
        $ruleDefinition = $this->ruleDefinitionRepository->findOneByIdentifier($ruleDefinitionCode);
        if (null === $ruleDefinition) {
            throw new NotFoundHttpException(sprintf('The rule definition "%s" is not found', $ruleDefinitionCode));
        }

        $this->ruleDefinitionUpdater->update($ruleDefinition, json_decode($request->getContent(), true));
        $violations = $this->validator->validate($ruleDefinition);
        if (0 < $violations->count()) {
            $errors = $this->violationNormalizer->normalize($violations, 'internal_api');

            return new JsonResponse($errors, Response::HTTP_BAD_REQUEST);
        }
        $this->ruleDefinitionSaver->save($ruleDefinition);

        return new JsonResponse($this->normalizer->normalize($ruleDefinition, 'internal_api'));
    }
}
