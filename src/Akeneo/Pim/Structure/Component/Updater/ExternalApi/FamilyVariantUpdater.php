<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Component\Updater\ExternalApi;

use Akeneo\Tool\Component\StorageUtils\Exception\ImmutablePropertyException;
use Akeneo\Tool\Component\StorageUtils\Exception\MandatoryPropertyException;
use Akeneo\Tool\Component\StorageUtils\Exception\PropertyException;
use Akeneo\Tool\Component\StorageUtils\Exception\UnknownPropertyException;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;

/**
 * Update the family variant properties
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FamilyVariantUpdater implements ObjectUpdaterInterface
{
    /** @var ObjectUpdaterInterface */
    private $familyUpdater;

    /**
     * @param ObjectUpdaterInterface $familyUpdater
     */
    public function __construct(ObjectUpdaterInterface $familyUpdater)
    {
        $this->familyUpdater = $familyUpdater;
    }

    /**
     * {@inheritdoc}
     *
     * @throws PropertyException
     */
    public function update($familyVariant, array $data, array $options = []): ObjectUpdaterInterface
    {
        if (isset($data['family'])) {
            throw UnknownPropertyException::unknownProperty('family');
        }
        $data['family'] = $options['familyCode'] ?? null;

        if (array_key_exists('variant_attribute_sets', $data) && is_array($data['variant_attribute_sets'])) {
            $this->validateVariantAttributeSets($data['variant_attribute_sets']);

            if (null !== $familyVariant->getId() && $familyVariant->getNumberOfLevel() !== count($data['variant_attribute_sets'])) {
                throw new ImmutablePropertyException(
                    'variant_attribute_sets',
                    count($data['variant_attribute_sets']),
                    static::class,
                    'The number of variant attribute sets cannot be changed.'
                );
            }
        }

        $this->familyUpdater->update($familyVariant, $data, $options);

        return $this;
    }

    /**
     * @param array $variantAttributeSets
     *
     * @throws MandatoryPropertyException
     */
    private function validateVariantAttributeSets(array $variantAttributeSets): void
    {
        foreach ($variantAttributeSets as $variantAttributeSet) {
            if (is_array($variantAttributeSet) && !array_key_exists('level', $variantAttributeSet)) {
                throw MandatoryPropertyException::mandatoryProperty('level', static::class);
            }
        }
    }
}
