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
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Source;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Target;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class AttributeSourceIsNotATargetValidator extends ConstraintValidator
{
    public function validate($command, Constraint $constraint)
    {
        $this->checkCommandType($command);
        if (!$constraint instanceof AttributeSourceIsNotATarget) {
            throw new UnexpectedTypeException($constraint, AttributeSourceIsNotATarget::class);
        }

        if (null === $command->transformations) {
            return;
        }

        $targets = [];
        foreach ($command->transformations as $transformation) {
            if (isset($transformation['target'])) {
                $targets[] = Target::createFromNormalized($transformation['target']);
            }
        }

        foreach ($command->transformations as $transformation) {
            if (isset($transformation['source'])) {
                $source = Source::createFromNormalized($transformation['source']);

                if ($this->thereIsATargetEqualToTheSource($source, $targets)) {
                    $this->context->buildViolation(
                        AttributeSourceIsNotATarget::ERROR_MESSAGE,
                        [
                            '%attribute_code%' => (string) $source->getAttributeCode(),
                        ]
                    )->atPath('transformations')->addViolation();
                }
            }
        }
    }

    /**
     * @param Target[] $targets
     */
    private function thereIsATargetEqualToTheSource(Source $source, array $targets): bool
    {
        foreach ($targets as $target) {
            if ($source->equals($target)) {
                return true;
            }
        }

        return false;
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
                    $command::class
                )
            );
        }
    }
}
