<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Validation\ProductMapping;

use Akeneo\Catalogs\Application\Persistence\AssetManager\FindOneAssetAttributeByIdentifierQueryInterface;
use Akeneo\Catalogs\Application\Persistence\Channel\GetChannelQueryInterface;
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
final class AssetAttributeSourceContainsValidScopeValidator extends ConstraintValidator
{
    public function __construct(
        private FindOneAssetAttributeByIdentifierQueryInterface $findOneAssetAttributeByIdentifierQuery,
        private GetChannelQueryInterface $getChannelQuery,
    ) {
    }

    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof AssetAttributeSourceContainsValidScope) {
            throw new UnexpectedTypeException($constraint, AssetAttributeSourceContainsValidScope::class);
        }

        if (!\is_array($value)) {
            throw new UnexpectedValueException($value, 'array');
        }

        /** @var AssetAttributeSource $value */

        $assetAttribute = $this->findOneAssetAttributeByIdentifierQuery->execute($value['sub_source']);

        if (null === $assetAttribute) {
            return;
        }

        $this->validateNonScopableSourceHasNoScope($assetAttribute, $value);
        $this->validateScopableSourceHasScope($assetAttribute, $value);
        $this->validateScopableSourceHasValidScope($assetAttribute, $value);
    }

    /**
     * @param AssetAttribute $assetAttribute
     * @param AssetAttributeSource $value
     */
    private function validateNonScopableSourceHasNoScope(array $assetAttribute, array $value): void
    {
        if ($assetAttribute['scopable'] || null === $value['sub_scope']) {
            return;
        }

        $this->context
            ->buildViolation('akeneo_catalogs.validation.product_mapping.source.sub_source.channel.not_empty')
            ->atPath('[sub_scope]')
            ->addViolation();
    }

    /**
     * @param AssetAttribute $assetAttribute
     * @param AssetAttributeSource $value
     */
    private function validateScopableSourceHasScope(array $assetAttribute, array $value): void
    {
        if (!$assetAttribute['scopable']) {
            return;
        }

        if (null === $value['sub_scope']) {
            $this->context
                ->buildViolation('akeneo_catalogs.validation.product_mapping.source.sub_source.channel.empty')
                ->atPath('[sub_scope]')
                ->addViolation();
        }
    }

    /**
     * @param AssetAttribute $assetAttribute
     * @param AssetAttributeSource $value
     */
    private function validateScopableSourceHasValidScope(array $assetAttribute, array $value): void
    {
        if (!$assetAttribute['scopable'] || null === $value['sub_scope']) {
            return;
        }

        $channel = $this->getChannelQuery->execute($value['sub_scope']);

        if (null === $channel) {
            $this->context
                ->buildViolation('akeneo_catalogs.validation.product_mapping.source.sub_source.channel.unknown')
                ->atPath('[sub_scope]')
                ->addViolation();
        }
    }
}
