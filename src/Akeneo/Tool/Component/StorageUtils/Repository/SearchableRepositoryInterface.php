<?php

namespace Akeneo\Tool\Component\StorageUtils\Repository;

/**
 * Searchable repository interface
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface SearchableRepositoryInterface
{
    const FETCH_LIMIT = 20;

    /**
     * Returns an array of option ids and values
     *
     * The returned format must be the one expected by select2 :
     *
     *  return [
     *      ['id' => 1, 'text' => 'Choice 1'],
     *      ['id' => 2, 'text' => 'Choice 2'],
     *  ];
     *
     * @param string $search
     * @param array  $options
     *
     * @return array
     */
    public function findBySearch($search = null, array $options = []);
}
