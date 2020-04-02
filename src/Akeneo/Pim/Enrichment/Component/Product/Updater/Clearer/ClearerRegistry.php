<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Updater\Clearer;

use Webmozart\Assert\Assert;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ClearerRegistry implements ClearerRegistryInterface
{
    /** @var ClearerInterface[] */
    private $clearers = [];

    public function __construct(iterable $clearers)
    {
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
        $this->clearers[] = $clearer;
    }

    /**
     * {@inheritDoc}
     */
    public function getClearer(string $property): ?ClearerInterface
    {
        foreach ($this->clearers as $clearer) {
            if ($clearer->supportsProperty($property)) {
                return $clearer;
            }
        }

        return null;
    }
}
