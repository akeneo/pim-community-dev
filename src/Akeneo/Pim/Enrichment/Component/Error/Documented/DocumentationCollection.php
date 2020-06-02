<?php
declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Error\Documented;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DocumentationCollection
{
    /** @var Documentation[] */
    private $collection;

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
     * @return array like
     * [
     *     [
     *         'message' => 'Please check your {attribute_settings}.',
     *         'params' => [
     *             '{attribute_settings}' => [
     *                 'route' => 'pim_enrich_attribute_index',
     *                 'params' => [],
     *                 'title' => 'Attributes settings',
     *                 'type' => 'route',
     *                 'needle' => '{attribute_settings}'
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
