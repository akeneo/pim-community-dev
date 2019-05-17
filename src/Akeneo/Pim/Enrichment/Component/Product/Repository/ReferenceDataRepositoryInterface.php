<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Repository;

use Doctrine\Common\Persistence\ObjectRepository;

/**
 * Reference data repository interface
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface ReferenceDataRepositoryInterface extends ObjectRepository
{
    /**
     * Returns an array of reference data ids and codes/labels according to the search that was performed
     *
     *  return array(
     *      array('id' => 1, 'text' => 'Reference Data 1'),
     *      array('id' => 2, 'text' => '[code2]'),
     *      array('id' => 3, 'text' => 'Reference Data 3'),
     *  );
     *
     * @param string $search
     * @param array  $options
     *
     * Possible options are:
     *    limit: the limit of reference data to return (if no search if performed, self::LIMIT_IF_NO_SEARCH is used)
     *    page: the page result to get
     *
     * @return array
     */
    public function findBySearch($search = null, array $options = []);

    /**
     * Return an array of reference data codes
     *
     * @param array $referenceDataCodes
     *
     * @return array
     */
    public function findCodesByIdentifiers(array $referenceDataCodes);
}
