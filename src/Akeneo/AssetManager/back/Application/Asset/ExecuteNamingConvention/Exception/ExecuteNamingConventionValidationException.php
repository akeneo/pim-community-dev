<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Application\Asset\ExecuteNamingConvention\Exception;

use Symfony\Component\Validator\ConstraintViolationListInterface;
use Webmozart\Assert\Assert;

final class ExecuteNamingConventionValidationException extends AbstractExecuteNamingConventionException
{
    private ConstraintViolationListInterface $violations;

    public function __construct(ConstraintViolationListInterface $violations)
    {
        parent::__construct();

        Assert::notEmpty($violations);
        $this->violations = $violations;
    }

    public function getViolations(): ConstraintViolationListInterface
    {
        return $this->violations;
    }
}
