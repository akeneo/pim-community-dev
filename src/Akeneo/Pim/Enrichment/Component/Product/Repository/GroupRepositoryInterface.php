<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Repository;

use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Doctrine\Common\Persistence\ObjectRepository;

/**
 * Group repository interface
 *
 * @author    Nicolas Dupont <janvier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface GroupRepositoryInterface extends IdentifiableObjectRepositoryInterface, ObjectRepository
{
    /**
     * @return mixed
     */
    public function createAssociationDatagridQueryBuilder();

    /**
     * @param string $dataLocale
     * @param int    $collectionId
     * @param string $search
     * @param array  $options
     *
     * @return array
     */
    public function getOptions($dataLocale, $collectionId = null, $search = '', array $options = []);
}
