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

use Akeneo\Pim\Automation\RuleEngine\Component\Updater\RuleDefinitionUpdaterInterface;
use Akeneo\Tool\Bundle\RuleEngineBundle\Doctrine\Common\Saver\RuleDefinitionSaver;
use Akeneo\Tool\Bundle\RuleEngineBundle\Model\RuleDefinition;
use Akeneo\Tool\Bundle\RuleEngineBundle\Normalizer\RuleDefinitionNormalizer;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CreateRuleDefinitionController
{
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
        RuleDefinitionUpdaterInterface $ruleDefinitionUpdater,
        RuleDefinitionSaver $ruleDefinitionSaver,
        RuleDefinitionNormalizer $ruleDefinitionNormalizer,
        NormalizerInterface $normalizer,
        ValidatorInterface $validator
    ) {
        $this->ruleDefinitionUpdater = $ruleDefinitionUpdater;
        $this->ruleDefinitionSaver = $ruleDefinitionSaver;
        $this->ruleDefinitionNormalizer = $ruleDefinitionNormalizer;
        $this->normalizer = $normalizer;
        $this->validator = $validator;
    }

    public function __invoke(Request $request): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $content = json_decode($request->getContent(), true);
        $ruleDefinition = new RuleDefinition();
        $this->ruleDefinitionUpdater->update($ruleDefinition, $content);
        $violations = $this->validator->validate($ruleDefinition);
        if (0 < $violations->count()) {
            $errors = $this->normalizer->normalize($violations, 'internal_api');

            return new JsonResponse($errors, Response::HTTP_BAD_REQUEST);
        }
        $this->ruleDefinitionSaver->save($ruleDefinition);

        return new JsonResponse($this->ruleDefinitionNormalizer->normalize($ruleDefinition));
    }
}
