<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Bundle\Query\PublicApi\AssociationType\Cache;

use Akeneo\Pim\Structure\Component\Query\PublicApi\AssociationType\GetAssociationTypeCodes;

/**
 * Cache for association type codes fetching. If there is too much codes to set in cache, no cache is done
 * in order to prevent memory problem (see static::CACHED_ITEM_LIMIT).
 *
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class CachedGetAssociationTypeCodes implements GetAssociationTypeCodes
{
    const CACHED_ITEM_LIMIT = 10000;

    /** @var GetAssociationTypeCodes */
    private $getAssociationTypeCodes;

    /** @var bool */
    private $canBeCached = true;

    /** @var null|string[] */
    private $cachedCodes = null;

    public function __construct(GetAssociationTypeCodes $getAssociationTypeCodes)
    {
        $this->getAssociationTypeCodes = $getAssociationTypeCodes;
    }

    public function findAll(): \Iterator
    {
        if (null !== $this->cachedCodes) {
            foreach ($this->cachedCodes as $code) {
                yield $code;
            }

            return;
        }

        if ($this->canBeCached) {
            $this->cachedCodes = [];
        }

        foreach ($this->getAssociationTypeCodes->findAll() as $code) {
            yield $code;

            if ($this->canBeCached) {
                $this->cachedCodes[] = $code;
                if (static::CACHED_ITEM_LIMIT < count($this->cachedCodes)) {
                    $this->canBeCached = false;
                    $this->cachedCodes = null;
                }
            }
        }
    }
}
