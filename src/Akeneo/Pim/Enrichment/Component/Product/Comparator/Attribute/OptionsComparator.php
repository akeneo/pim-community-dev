<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Comparator\Attribute;

use Akeneo\Pim\Enrichment\Component\Product\Comparator\ComparatorInterface;
use Webmozart\Assert\Assert;

/**
 * Comparator which calculate change set for collections of options
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class OptionsComparator implements ComparatorInterface
{
    /** @var array */
    protected $types;

    /**
     * @param array $types
     */
    public function __construct(array $types)
    {
        $this->types = $types;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($type)
    {
        return \in_array($type, $this->types);
    }

    /**
     * {@inheritdoc}
     */
    public function compare($data, $originals)
    {
        try {
            Assert::isArray($data['data']);
            Assert::allString($data['data']);
        } catch (\InvalidArgumentException) {
            return $data;
        }

        $default = ['locale' => null, 'scope' => null, 'data' => []];
        $originals = \array_merge($default, $originals);

        $originalsToLower = \array_map('strtolower', $originals['data']);
        $dataToLower = \array_map('strtolower', $data['data'] ?? []);

        \sort($originalsToLower);
        \sort($dataToLower);

        if ($dataToLower === $originalsToLower) {
            return null;
        }

        return $data;
    }
}
