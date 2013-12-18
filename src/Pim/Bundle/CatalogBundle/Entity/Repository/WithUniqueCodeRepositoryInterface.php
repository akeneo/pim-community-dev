<?php

namespace Pim\Bundle\CatalogBundle\Entity\Repository;

/**
 * Interface for repositories of unique code entities
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface WithUniqueCodeRepositoryInterface
{
    /**
     * Returns an array containing the name of the unique code properties
     *
     * @return array
     */
    public function getUniqueCodeProperties();

    /**
     * Find an entity by unique code
     *
     * @var string $code
     *
     * @return object
     */
    public function findByUniqueCode($code);

    /**
     * Find an entity by unique code using a data array
     *
     * @var array $data
     *
     * @return object
     */
    public function findByDataUniqueCode(array $data);
}
