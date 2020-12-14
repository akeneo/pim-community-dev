<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Component\Validator\Constraints;

use Akeneo\Pim\Structure\Component\Query\InternalApi\GetBlacklistedAttributeJobExecutionIdInterface;
use Akeneo\Pim\Structure\Component\Query\InternalApi\IsAttributeCodeBlacklistedInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class BlacklistedAttributeCodeValidator extends ConstraintValidator
{
    private const JOB_TRACKER_ROUTE = 'pim_enrich_job_tracker_show';

    protected IsAttributeCodeBlacklistedInterface $isAttributeCodeBlacklisted;
    private GetBlacklistedAttributeJobExecutionIdInterface $getBlacklistedAttributeJobExecutionId;
    private Translator $translator;
    private RouterInterface $router;

    public function __construct(
        IsAttributeCodeBlacklistedInterface $isAttributeCodeBlacklisted,
        GetBlacklistedAttributeJobExecutionIdInterface $getBlacklistedAttributeJobExecutionId,
        Translator $translator,
        RouterInterface $router
    ) {
        $this->isAttributeCodeBlacklisted = $isAttributeCodeBlacklisted;
        $this->getBlacklistedAttributeJobExecutionId = $getBlacklistedAttributeJobExecutionId;
        $this->translator = $translator;
        $this->router = $router;
    }

    /**
     * Don't allow creating an attribute if it's code is blacklisted
     *
     * @param string $attributeCode
     * @throws \Exception
     */
    public function validate($attributeCode, Constraint $constraint): void
    {
        if (!$constraint instanceof BlacklistedAttributeCode) {
            throw new UnexpectedTypeException($constraint, BlacklistedAttributeCode::class);
        }

        if (is_string($attributeCode) && $this->isAttributeCodeBlacklisted->execute($attributeCode)) {
            $this->addInternalViolation($attributeCode, $constraint);
            $this->context
                ->buildViolation($constraint->message)
                ->addViolation();
        }
    }

    private function addInternalViolation(string $attributeCode, BlacklistedAttributeCode $constraint): void
    {
        $jobExecutionId = $this->getBlacklistedAttributeJobExecutionId->forAttributeCode($attributeCode);
        $jobExecutionLink = sprintf('#%s', $this->router->generate(self::JOB_TRACKER_ROUTE, ['id' => $jobExecutionId]));
        $internalApiMessage = $this->translator->trans(
            $constraint->internalAPIMessage,
            ['{{ link }}' => $jobExecutionLink],
            'validators'
        );

        $constraint->payload['internal_api_message'] = $internalApiMessage;
    }
}
