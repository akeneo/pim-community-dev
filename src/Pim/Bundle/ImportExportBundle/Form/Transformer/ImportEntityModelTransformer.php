<?php

namespace Pim\Bundle\ImportExportBundle\Form\Transformer;

use Symfony\Component\Form\DataTransformerInterface;
use Pim\Bundle\ImportExportBundle\Cache\EntityCache;

/**
 * Transform entity codes in entity arrays
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ImportEntityModelTransformer implements DataTransformerInterface
{
    /**
     * @var EntityCache
     */
    protected $entityCache;

    /**
     * @var array
     */
    protected $options;

    /**
     * Constructor
     *
     * @param EntityCache $entityCache
     * @param array       $options
     */
    public function __construct(EntityCache $entityCache, array $options)
    {
        $this->entityCache = $entityCache;
        $this->options = $options;
    }

    /**
     * {@inheritdoc}
     */
    public function reverseTransform($value)
    {
        if (null == $value) {
            return;
        }

        $class = $this->options['class'];
        $entityCache = $this->entityCache;
        $transform = function ($value) use ($class, $entityCache) {
            return $entityCache->find($class, $value);
        };

        //TODO: add alert if entity not found ?
        return ($this->options['multiple'])
            ? array_filter(array_map($transform, preg_split('/\s*,\s*/', $value)), 'is_object')
            : $transform($value);
    }

    /**
     * @inheritdoc
     */
    public function transform($value)
    {
        return '';
    }
}
