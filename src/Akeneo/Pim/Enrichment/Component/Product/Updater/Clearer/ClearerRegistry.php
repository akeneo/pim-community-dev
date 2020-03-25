<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Updater\Clearer;

use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
use Webmozart\Assert\Assert;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ClearerRegistry implements ClearerRegistryInterface
{
    /** @var GetAttributes */
    private $getAttributes;

    /** @var AttributeClearerInterface[] */
    private $attributeClearers = [];

    /** @var FieldClearerInterface[] */
    private $fieldClearers = [];

    public function __construct(GetAttributes $getAttributes, iterable $clearers)
    {
        $this->getAttributes = $getAttributes;
        foreach ($clearers as $clearer) {
            Assert::implementsInterface($clearer, ClearerInterface::class);
            $this->register($clearer);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function register(ClearerInterface $clearer): void
    {
        if (!$clearer instanceof AttributeClearerInterface && !$clearer instanceof FieldClearerInterface) {
            throw new \InvalidArgumentException(sprintf(
                'Clearer must be an instance of %s or %s, %s given.',
                AttributeClearerInterface::class,
                FieldClearerInterface::class,
                get_class($clearer)
            ));
        }

        if ($clearer instanceof AttributeClearerInterface) {
            $this->attributeClearers[] = $clearer;
        }

        if ($clearer instanceof FieldClearerInterface) {
            $this->fieldClearers[] = $clearer;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getClearer(string $property): ?ClearerInterface
    {
        $attribute = $this->getAttributes->forCode($property);
        if (null !== $attribute) {
            return $this->getAttributeClearer($attribute);
        }

        return $this->getFieldClearer($property);
    }

    /**
     * {@inheritDoc}
     */
    public function getAttributeClearer(Attribute $attribute): ?AttributeClearerInterface
    {
        foreach ($this->attributeClearers as $clearer) {
            if ($clearer->supportsAttribute($attribute)) {
                return $clearer;
            }
        }

        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function getFieldClearer(string $field): ?FieldClearerInterface
    {
        foreach ($this->fieldClearers as $clearer) {
            if ($clearer->supportsField($field)) {
                return $clearer;
            }
        }

        return null;
    }
}
