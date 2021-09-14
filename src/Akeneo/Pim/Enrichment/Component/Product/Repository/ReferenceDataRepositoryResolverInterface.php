<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Repository;

use Doctrine\Persistence\ObjectRepository;

/**
 * Resolves the repository given a reference data type
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface ReferenceDataRepositoryResolverInterface
{
    public function resolve(string $referenceDataType): ObjectRepository;
}
