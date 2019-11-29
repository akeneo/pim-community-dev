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
 * A transformation filename is unique if:
 *  - source is the same (attribute, channel and locale)
 *  - prefix is the same
 *  - suffix is the same
 *
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class TransformationFilenameIsUniqueValidator extends ConstraintValidator
{
    public function validate($command, Constraint $constraint)
    {
        $this->checkConstraintType($constraint);
        $this->checkCommandType($command);

        if ($command->transformations === null) {
            return;
        }

        foreach (array_values($command->transformations) as $index => $transformation) {
            $subTransformations = array_slice($command->transformations, $index + 1);
            if ($this->thereIsATransformationWithSameTargetFilename($transformation, $subTransformations)) {
                $this->context->buildViolation(TransformationFilenameIsUnique::ERROR_MESSAGE)
                    ->setParameter('%filename_prefix%', trim($transformation['filename_prefix']))
                    ->setParameter('%filename_suffix%', trim($transformation['filename_suffix']))
                    ->setParameter('%attribute_code%', trim($transformation['source']['attribute']))
                    ->addViolation();
            }
        }
    }

    private function thereIsATransformationWithSameTargetFilename(array $transformation, array $transformations): bool
    {
        foreach ($transformations as $oneTransformation) {
            if ($this->sourcesAreEqual($transformation['source'], $oneTransformation['source'])
                && trim($transformation['filename_prefix']) === trim($oneTransformation['filename_prefix'])
                && trim($transformation['filename_suffix']) === trim($oneTransformation['filename_suffix'])
            ) {
                return true;
            }
        }

        return false;
    }

    private function sourcesAreEqual(array $source1, array $source2): bool
    {
        return $source1['attribute'] === $source2['attribute']
            && $source1['channel'] === $source2['channel']
            && $source1['locale'] === $source2['locale']
            ;
    }

    /**
     * @throws UnexpectedTypeException
     */
    private function checkConstraintType(Constraint $constraint): void
    {
        if (!$constraint instanceof TransformationFilenameIsUnique) {
            throw new UnexpectedTypeException($constraint, TransformationFilenameIsUnique::class);
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
}
