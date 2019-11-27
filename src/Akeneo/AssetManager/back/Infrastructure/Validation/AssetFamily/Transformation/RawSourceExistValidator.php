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

namespace Akeneo\AssetManager\Infrastructure\Validation\AssetFamily\Transformation;

use Akeneo\AssetManager\Domain\Model\Asset\Value\ChannelReference;
use Akeneo\AssetManager\Domain\Model\Asset\Value\LocaleReference;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Source;
use Akeneo\AssetManager\Domain\Model\Attribute\AbstractAttribute;
use Akeneo\AssetManager\Domain\Repository\AttributeRepositoryInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Webmozart\Assert\Assert;

class RawSourceExistValidator extends ConstraintValidator
{
    /** @var AttributeRepositoryInterface */
    private $attributeRepository;

    public function __construct(AttributeRepositoryInterface $attributeRepository)
    {
        $this->attributeRepository = $attributeRepository;
    }

    public function validate($rawSource, Constraint $constraint)
    {
        if (!$constraint instanceof RawSourceExist) {
            throw new UnexpectedTypeException($constraint, RawSourceExist::class);
        }

        Assert::isArray($rawSource, 'source must be an array.');

        $attributes = $this->attributeRepository->findByAssetFamily($constraint->getAssetFamilyIdentifier());
        $foundAttribute = null;
        foreach ($attributes as $attribute) {
            if ($attribute->getCode()->__toString() === $rawSource['attribute']) {
                $foundAttribute = $attribute;

                break;
            }
        }

        if ($foundAttribute instanceof AbstractAttribute) {
            $this->validateAttribute($rawSource, $foundAttribute);
            return;
        }

        $this->context->buildViolation(
            RawSourceExist::ATTRIBUTE_NOT_FOUND_ERROR,
            ['%attribute_code%' => $rawSource['attribute']]
        )->atPath('source')->addViolation();
    }

    private function validateAttribute(array $source, AbstractAttribute $attribute): void
    {
        try {
            Source::create(
                $attribute,
                ChannelReference::createfromNormalized($source['channel']),
                LocaleReference::createFromNormalized($source['locale'])
            );
        } catch (\Exception $e) {
            $this->context->buildViolation($e->getMessage())->atPath('source')->addViolation();
        }
    }
}
