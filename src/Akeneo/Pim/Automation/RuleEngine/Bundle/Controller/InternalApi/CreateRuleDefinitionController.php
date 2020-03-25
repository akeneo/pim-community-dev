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
use Akeneo\Tool\Bundle\RuleEngineBundle\Model\RuleDefinition;
use Akeneo\Tool\Bundle\RuleEngineBundle\Repository\RuleDefinitionRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CreateRuleDefinitionController
{
    /** @var ObjectUpdaterInterface */
    private $ruleDefinitionUpdater;

    /** @var RuleDefinitionSaver */
    private $ruleDefinitionSaver;

    /** @var ValidatorInterface */
    private $validator;

    /** @var ViolationNormalizer */
    private $violationNormalizer;

    /** @var NormalizerInterface */
    private $normalizer;

    public function __construct(
        ObjectUpdaterInterface $ruleDefinitionUpdater,
        RuleDefinitionSaver $ruleDefinitionSaver,
        ValidatorInterface $validator,
        ViolationNormalizer $violationNormalizer,
        NormalizerInterface $normalizer
    ) {
        $this->ruleDefinitionUpdater = $ruleDefinitionUpdater;
        $this->ruleDefinitionSaver = $ruleDefinitionSaver;
        $this->validator = $validator;
        $this->violationNormalizer = $violationNormalizer;
        $this->normalizer = $normalizer;
    }

    public function __invoke(Request $request)
    {
        $ruleDefinition = new RuleDefinition();
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
