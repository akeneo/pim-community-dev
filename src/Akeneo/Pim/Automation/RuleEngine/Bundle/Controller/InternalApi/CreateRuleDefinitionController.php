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
use Akeneo\Tool\Bundle\RuleEngineBundle\Model\RuleDefinition;
use Akeneo\Tool\Bundle\RuleEngineBundle\Normalizer\RuleDefinitionNormalizer;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
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

    /** @var SecurityFacade */
    private $securityFacade;

    public function __construct(
        RuleDefinitionUpdaterInterface $ruleDefinitionUpdater,
        RuleDefinitionSaver $ruleDefinitionSaver,
        RuleDefinitionNormalizer $ruleDefinitionNormalizer,
        NormalizerInterface $normalizer,
        ValidatorInterface $validator,
        SecurityFacade $securityFacade
    ) {
        $this->ruleDefinitionUpdater = $ruleDefinitionUpdater;
        $this->ruleDefinitionSaver = $ruleDefinitionSaver;
        $this->ruleDefinitionNormalizer = $ruleDefinitionNormalizer;
        $this->normalizer = $normalizer;
        $this->validator = $validator;
        $this->securityFacade = $securityFacade;
    }

    public function __invoke(Request $request): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        if (!$this->securityFacade->isGranted('pimee_catalog_rule_rule_create_permissions')) {
            throw new AccessDeniedException();
        }

        $data = json_decode($request->getContent(), true);

        $command = new CreateOrUpdateRuleCommand($data);
        $violations = $this->validator->validate($command, null, ['Default', 'create']);
        if ($violations->count()) {
            $errors = $this->normalizer->normalize($violations, 'internal_api');

            return new JsonResponse($errors, Response::HTTP_BAD_REQUEST);
        }

        $ruleDefinition = new RuleDefinition();
        $this->ruleDefinitionUpdater->update($ruleDefinition, $command->toArray(true));
        $this->ruleDefinitionSaver->save($ruleDefinition);

        return new JsonResponse($this->ruleDefinitionNormalizer->normalize($ruleDefinition));
    }
}
