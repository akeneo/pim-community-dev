<?php

namespace Oro\Bundle\PimDataGridBundle\Normalizer\InternalApi;

use Oro\Bundle\PimDataGridBundle\Entity\DatagridView;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Structured normalizer for DatagridView
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class DatagridViewNormalizer implements NormalizerInterface
{
    /** @var array */
    protected $supportedFormat = ['internal_api'];

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = []): \ArrayObject|array|string|int|float|bool|null
    {
        return [
            'id'             => (int) $object->getId(),
            'owner_id'       => (int) $object->getOwner()->getId(),
            'label'          => (string) $object->getLabel(),
            'type'           => (string) $object->getType(),
            'datagrid_alias' => (string) $object->getDatagridAlias(),
            'columns'        => explode(',', $object->getOrder()),
            'filters'        => (string) $object->getFilters(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null, array $context = []): bool
    {
        return $data instanceof DatagridView && in_array($format, $this->supportedFormat);
    }

    public function getSupportedTypes(?string $format): array
    {
        return ['*' => true];
    }
}
