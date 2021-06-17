<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Infrastructure\Validation\AssetFamily;

use Akeneo\AssetManager\Application\AssetFamily\CreateAssetFamily\CreateAssetFamilyCommand;
use Akeneo\AssetManager\Application\AssetFamily\EditAssetFamily\EditAssetFamilyCommand;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class ThereShouldBeLessRuleTemplateThanLimitValidator extends ConstraintValidator
{
    private int $ruleTemplateByAssetFamilyLimit;

    public function __construct(int $ruleTemplateByAssetFamilyLimit)
    {
        $this->ruleTemplateByAssetFamilyLimit = $ruleTemplateByAssetFamilyLimit;
    }

    public function validate($command, Constraint $constraint): void
    {
        $this->checkConstraintType($constraint);
        $this->checkCommandType($command);
        $this->validateCommand($command);
    }

    /**
     * @throws UnexpectedTypeException
     */
    private function checkConstraintType(Constraint $constraint): void
    {
        if (!$constraint instanceof ThereShouldBeLessRuleTemplateThanLimit) {
            throw new UnexpectedTypeException($constraint, self::class);
        }
    }

    /**
     * @throws \InvalidArgumentException
     */
    private function checkCommandType($command): void
    {
        if (!$command instanceof CreateAssetFamilyCommand && !$command instanceof EditAssetFamilyCommand) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Expected argument to be of class "%s" or "%s", "%s" given',
                    CreateAssetFamilyCommand::class,
                    EditAssetFamilyCommand::class,
                    get_class($command)
                )
            );
        }
    }

    private function validateCommand($command): void
    {
        if ($command->productLinkRules === null) {
            return;
        }

        $total = count($command->productLinkRules);

        if ($total > $this->ruleTemplateByAssetFamilyLimit) {
            $this->context->buildViolation(ThereShouldBeLessRuleTemplateThanLimit::ERROR_MESSAGE)
                ->setParameter('%asset_family_identifier%', $command->identifier)
                ->setParameter('%limit%', $this->ruleTemplateByAssetFamilyLimit)
                ->atPath('labels')
                ->addViolation();
        }
    }
}
