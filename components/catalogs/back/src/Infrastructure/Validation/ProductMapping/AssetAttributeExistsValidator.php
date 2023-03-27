<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Validation\ProductMapping;

use Akeneo\Catalogs\Application\Persistence\AssetManager\FindOneAssetAttributeByIdentifierQueryInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @psalm-suppress PropertyNotSetInConstructor
 *
 * @phpstan-type AssetAttributeSource array{sub_source: string, sub_scope: string|null, sub_locale: string|null}
 * @phpstan-import-type AssetAttribute from FindOneAssetAttributeByIdentifierQueryInterface
 */
final class AssetAttributeExistsValidator extends ConstraintValidator
{
    public function __construct(
        private FindOneAssetAttributeByIdentifierQueryInterface $findOneAssetAttributeByIdentifierQuery,
    ) {
    }

    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof AssetAttributeExists) {
            throw new UnexpectedTypeException($constraint, AssetAttributeExists::class);
        }

        if (!\is_array($value)) {
            throw new UnexpectedValueException($value, 'array');
        }

        /** @var AssetAttributeSource $value */

        $assetAttribute = $this->findOneAssetAttributeByIdentifierQuery->execute($value['sub_source']);

        if (null === $assetAttribute) {
            $this->context
                ->buildViolation('akeneo_catalogs.validation.product_mapping.source.sub_source.invalid')
                ->atPath('[sub_locale]')
                ->addViolation();
        }
    }
}
