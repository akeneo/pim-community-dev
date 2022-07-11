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
use Webmozart\Assert\Assert;

/**
 * A transformation filename is not unique if there is another transformation with:
 *  - the same source (attribute, channel and locale),
 *  - the same filename prefix
 *  - the same filename suffix
 *
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class TransformationFilenameIsUniqueValidator extends ConstraintValidator
{
    public function validate($command, Constraint $constraint)
    {
        $this->checkConstraintType($constraint);
        Assert::isInstanceOfAny($command, [CreateAssetFamilyCommand::class, EditAssetFamilyCommand::class]);

        if ($command->transformations === null) {
            return;
        }

        foreach (array_values($command->transformations) as $index => $transformation) {
            $subTransformations = array_slice($command->transformations, $index + 1);
            if ($this->thereIsATransformationWithSameTargetFilename($transformation, $subTransformations)) {
                $this->context->buildViolation(TransformationFilenameIsUnique::ERROR_MESSAGE)
                    ->setParameter('%filename_prefix%', $transformation['filename_prefix'] ?? '')
                    ->setParameter('%filename_suffix%', $transformation['filename_suffix'] ?? '')
                    ->setParameter('%attribute_code%', $transformation['source']['attribute'])
                    ->atPath('transformations')
                    ->addViolation();
            }
        }
    }

    private function thereIsATransformationWithSameTargetFilename(array $transformation, array $transformations): bool
    {
        foreach ($transformations as $otherTransformation) {
            if ($this->sourcesAreEqual($transformation['source'], $otherTransformation['source'])
                && ($transformation['filename_prefix'] ?? '') === ($otherTransformation['filename_prefix'] ?? '')
                && ($transformation['filename_suffix'] ?? '') === ($otherTransformation['filename_suffix'] ?? '')
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
}
