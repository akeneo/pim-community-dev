<?php

declare(strict_types=1);

namespace Akeneo\EnrichedEntity\back\Infrastructure\Normalizer;

use Akeneo\EnrichedEntity\back\Domain\Model\EnrichedEntity\EnrichedEntity as EnrichedEntityModel;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class EnrichedEntity implements NormalizerInterface
{
    /** @var string[] */
    protected $supportedFormats = ['internal_api'];

    /**
     * {@inheritdoc}
     *
     * @param EnrichedEntityModel $enrichedEntity
     */
    public function normalize($enrichedEntity, $format = null, array $context = []): array
    {
        $normalizedEnrichedEntity = [
            'identifier' => (string) $enrichedEntity->getIdentifier(),
            'labels'     => $this->normalizeLabels($enrichedEntity)
        ];

        return $normalizedEnrichedEntity;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null): bool
    {
        return $data instanceof EnrichedEntityModel && in_array($format, $this->supportedFormats);
    }

    /**
     * Normalize the label collection
     *
     * @param EnrichedEntityModel $enrichedEntity
     *
     * @return array
     */
    private function normalizeLabels(EnrichedEntityModel $enrichedEntity): array
    {
        $localeCodes = $enrichedEntity->getLabelCodes();

        return array_reduce(
            $localeCodes,
            function (array $result, string $localeCode) use ($enrichedEntity) {
                $result[$localeCode] = $enrichedEntity->getLabel($localeCode);

                return $result;
            },
            []
        );
    }
}
