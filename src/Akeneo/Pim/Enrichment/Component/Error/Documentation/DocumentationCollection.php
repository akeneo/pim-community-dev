<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Error\Documentation;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DocumentationCollection
{
    /** @var Documentation[] */
    private $collection;

    /**
     * @param Documentation[] $documentations
     */
    public function __construct(array $documentations)
    {
        foreach ($documentations as $documentation) {
            if (!$documentation instanceof Documentation) {
                throw new \InvalidArgumentException(sprintf(
                    'Class "%s" can only contain collection of "%s", instance of "%s" given.',
                    self::class,
                    Documentation::class,
                    get_class($documentation)
                ));
            }
        }
        $this->collection = $documentations;
    }

    /**
     * @return array<array{message: string, parameters: array<string, array<string, string|array>>}>
     * Example:
     * [
     *     [
     *         'message' => 'Please check your {attribute_settings}.',
     *         'parameters' => [
     *             'attribute_settings' => [
     *                 'route' => 'pim_enrich_attribute_index',
     *                 'routeParameters' => [],
     *                 'title' => 'Attributes settings',
     *                 'type' => 'route',
     *             ],
     *         ],
     *     ],
     * ]
     */
    public function normalize(): array
    {
        $normalizedCollection = [];
        foreach ($this->collection as $documentation) {
            $normalizedCollection[] = $documentation->normalize();
        }

        return $normalizedCollection;
    }
}
