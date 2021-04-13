<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\ReferenceEntity\Infrastructure\Connector\ArrayConverter\FlatToStandard;

use Akeneo\ReferenceEntity\Domain\Model\Attribute\ImageAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\OptionCollectionAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\RecordCollectionAttribute;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\AttributeDetails;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\FindAttributesDetailsInterface;
use Akeneo\Tool\Component\Connector\ArrayConverter\ArrayConverterInterface;
use Akeneo\Tool\Component\Connector\ArrayConverter\FieldsRequirementChecker;
use Akeneo\Tool\Component\Connector\Exception\DataArrayConversionException;
use Webmozart\Assert\Assert;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 */
final class Record implements ArrayConverterInterface
{
    public const DIRECTORY_PATH_OPTION_KEY = 'directory_path';

    private FieldsRequirementChecker $fieldsChecker;
    private FindAttributesDetailsInterface $findAttributeDetails;
    private array $cachedAttributes = [];

    public function __construct(
        FieldsRequirementChecker $fieldsChecker,
        FindAttributesDetailsInterface $findAttributeDetails
    ) {
        $this->fieldsChecker = $fieldsChecker;
        $this->findAttributeDetails = $findAttributeDetails;
    }

    /**
     * {@inheritdoc}
     *
     * Converts flat csv array to standard structured array:
     *
     * Before:
     * [
     *      'referenceEntityIdentifier'  => 'brand',
     *      'code'                       => 'ref1',
     *      'label-en_US'                => 'My ref entity',
     *      'label-fr_FR'                => 'Ma ref entité',
     *      'attribute1'                 => 'data1',
     *      'attribute2-en_US'           => 'data2-en',
     *      'attribute2-fr_FR'           => 'data2-fr',
     *      'attribute3-ecommerce'       => 'data3',
     *      'attribute4-en_US-ecommerce' => 'data4',
     * ]
     *
     * After:
     * [
     *      'reference_entity_identifier' => 'brand',
     *      'code' => 'ref1',
     *      'values' => [
     *          'label' => [
     *              [
     *                  'channel' => null,
     *                  'locale' => 'en_US',
     *                  'data' => 'My ref entity',
     *              ],
     *              [
     *                  'channel' => null,
     *                  'locale' => 'fr_FR',
     *                  'data' => ''Ma ref entité',
     *              ],
     *          ],
     *          'attribute1' => [[
     *              'channel' => null,
     *              'locale' => null,
     *              'data' => 'data1',
     *          ]],
     *          'attribute2' => [
     *              [
     *                  'channel' => null,
     *                  'locale' => 'en_US',
     *                  'data' => 'data2-en',
     *              ],
     *              [
     *                  'channel' => null,
     *                  'locale' => 'fr_FR',
     *                  'data' => 'data2-fr',
     *              ],
     *          ],
     *          'attribute3' => [[
     *              'channel' => 'ecommerce',
     *              'locale' => null,
     *              'data' => 'data3',
     *          ]],
     *          'attribute4' => [[
     *              'channel' => 'ecommerce',
     *              'locale' => 'en_US',
     *              'data' => 'data4',
     *          ]],
     *      ],
     * ]
     */
    public function convert(array $item, array $options = [])
    {
        Assert::keyExists($options, self::DIRECTORY_PATH_OPTION_KEY);
        Assert::string($options[self::DIRECTORY_PATH_OPTION_KEY]);
        $this->fieldsChecker->checkFieldsPresence($item, ['referenceEntityIdentifier', 'code']);

        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString($item['referenceEntityIdentifier'] ?? '');
        $convertedItem = ['values' => ['label' => []]];
        foreach ($item as $field => $data) {
            $convertedItem = $this->convertField(
                $convertedItem,
                $options[self::DIRECTORY_PATH_OPTION_KEY],
                $referenceEntityIdentifier,
                $field,
                (string) $data
            );
        }

        return $convertedItem;
    }

    private function convertField(
        array $convertedItem,
        string $directoryPath,
        ReferenceEntityIdentifier $referenceEntityIdentifier,
        string $field,
        string $data
    ): array {
        if ('' === trim($field)) {
            return $convertedItem;
        }

        if ('referenceEntityIdentifier' === $field) {
            $convertedItem['reference_entity_identifier'] = $data;

            return $convertedItem;
        } elseif ('code' === $field) {
            $convertedItem['code'] = $data;

            return $convertedItem;
        }

        $tokens = explode('-', $field);

        if ('label' === $tokens[0]) {
            if (!array_key_exists($tokens[0], $convertedItem['values'])) {
                $convertedItem['values'][$tokens[0]] = [];
            }

            $convertedItem['values']['label'][] = [
                'locale' => $tokens[1] ?? null,
                'channel' => null,
                'data' => $data,
            ];
        } else {
            $attributeDetails = $this->getAttributeDetails($tokens[0], $referenceEntityIdentifier);
            if (null === $attributeDetails) {
                // If attribute does not belong to ref entity and the value is empty, we skip it.
                // This behavior allows to have records with different reference entities in the same import.
                if ('' === $data) {
                    return $convertedItem;
                }

                // On contrary when we try to put a non empty value in an attribute that does not belong to the
                // reference entity, we throw an exception.
                throw new DataArrayConversionException(\sprintf(
                    'Unable to find the "%s" attribute in the "%s" reference entity',
                    $tokens[0],
                    $referenceEntityIdentifier
                ));
            }

            $convertedValue = $this->convertValue(
                $directoryPath,
                $attributeDetails,
                $field,
                $data
            );

            if (null !== $convertedValue) {
                if (!array_key_exists($tokens[0], $convertedItem['values'])) {
                    $convertedItem['values'][$tokens[0]] = [];
                }

                $convertedItem['values'][$tokens[0]][] = $convertedValue;
            }
        }

        return $convertedItem;
    }

    /**
     * Convert value from flat to standard.
     * If the channel and/or scope are not specified while the attribute needs them, AND the value is empty,
     * then we skip the conversion by returning null. This behavior is needed because we can import some records
     * with several reference entities, so if an attribute code is localizable in a ref entity and not in another then
     * the user can just let the column empty for attributes that do not belong to the reference entity and
     * the import will not return errors.
     */
    private function convertValue(
        string $directoryPath,
        AttributeDetails $attributeDetails,
        string $field,
        string $data
    ): ?array {
        $tokens = explode('-', $field);
        $dataIsEmpty = '' === $data;
        if (in_array($attributeDetails->type, [
            RecordCollectionAttribute::ATTRIBUTE_TYPE,
            OptionCollectionAttribute::ATTRIBUTE_TYPE,
        ])) {
            $data = $dataIsEmpty ? [] : explode(',', $data);
        } elseif (!empty($data) && ImageAttribute::ATTRIBUTE_TYPE === $attributeDetails->type) {
            $data = sprintf('%s%s%s', $directoryPath, DIRECTORY_SEPARATOR, $data);
        }

        $convertedValue = ['locale' => null, 'channel' => null, 'data' => $data];
        if (3 === count($tokens)) {
            if ($dataIsEmpty && (!$attributeDetails->valuePerChannel || !$attributeDetails->valuePerLocale)) {
                return null;
            }

            $convertedValue['locale'] = $tokens[1];
            $convertedValue['channel'] = $tokens[2];
        } elseif (2 === count($tokens)) {
            if ($attributeDetails->valuePerChannel && !$attributeDetails->valuePerLocale) {
                $convertedValue['channel'] = $tokens[1];
            } elseif (!$attributeDetails->valuePerChannel && $attributeDetails->valuePerLocale) {
                $convertedValue['locale'] = $tokens[1];
            } elseif ($dataIsEmpty) {
                return null;
            } else {
                // The validation will trigger an error
                $convertedValue['locale'] = $tokens[1];
            }
        } else {
            if ($dataIsEmpty && ($attributeDetails->valuePerChannel || $attributeDetails->valuePerLocale)) {
                return null;
            }
        }

        return $convertedValue;
    }

    private function getAttributeDetails(
        string $attributeCode,
        ReferenceEntityIdentifier $referenceEntityIdentifier
    ): ?AttributeDetails {
        $normalizedReferenceEntityIdentifier = $referenceEntityIdentifier->normalize();
        if (!array_key_exists($normalizedReferenceEntityIdentifier, $this->cachedAttributes)) {
            $this->cachedAttributes[$normalizedReferenceEntityIdentifier] = $this
                ->getIndexedAttributes($referenceEntityIdentifier);
        }

        return $this->cachedAttributes[$normalizedReferenceEntityIdentifier][$attributeCode] ?? null;
    }

    private function getIndexedAttributes(ReferenceEntityIdentifier $referenceEntityIdentifier): array
    {
        $attributesDetails = $this->findAttributeDetails->find($referenceEntityIdentifier);

        $indexedAttributeDetails = [];
        foreach ($attributesDetails as $attributeDetail) {
            $indexedAttributeDetails[$attributeDetail->code] = $attributeDetail;
        }

        return $indexedAttributeDetails;
    }
}
