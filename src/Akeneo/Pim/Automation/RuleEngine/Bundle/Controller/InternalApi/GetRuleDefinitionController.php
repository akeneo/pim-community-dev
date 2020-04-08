<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\RuleEngine\Bundle\Controller\InternalApi;

use Akeneo\Tool\Bundle\RuleEngineBundle\Repository\RuleDefinitionRepositoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Get rule definition controller
 *
 * @author Evrard Caron <evrard.caron@akeneo.com>
 */
class GetRuleDefinitionController
{
    /** @var RuleDefinitionRepositoryInterface */
    private $ruleDefinitionRepository;

    /** @var NormalizerInterface */
    private $normalizer;

    public function __construct(
        RuleDefinitionRepositoryInterface $ruleDefinitionRepository,
        NormalizerInterface $normalizer
    ) {
        $this->ruleDefinitionRepository = $ruleDefinitionRepository;
        $this->normalizer = $normalizer;
    }

    public function __invoke(string $ruleCode, Request $request)
    {
        return new JsonResponse('foo');
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $ruleDefinition = $this->ruleDefinitionRepository->findOneByIdentifier($ruleCode);

        if (null === $ruleDefinition) {
            throw new NotFoundHttpException(
                sprintf('The "%s" rule definition is not found', $ruleCode)
            );
        }

        return new JsonResponse($this->normalizer->normalize($ruleDefinition, 'array'));
    }
}
