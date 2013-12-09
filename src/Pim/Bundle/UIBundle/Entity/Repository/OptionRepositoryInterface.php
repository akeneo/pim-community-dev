<?php

namespace Pim\Bundle\UIBundle\Entity\Repository;

/**
 * Interface for option repositories
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface OptionRepositoryInterface
{
    /**
     * Returns an array of option ids and values
     *
     * @param string $dataLocale
     * @param mixed  $collectionId
     * @param string $search
     * @param array  $options
     *
     * @return array
     */
    public function getOptions($dataLocale, $collectionId = null, $search = '', array $options=array());

    /**
     * Returns the entity corresponding to a given id
     *
     * @param mixed $id
     * @param mixed $collectionId
     * @param array $options
     *
     * @return object
     */
    public function getOption($id, $collectionId = null, array $options = array());

    /**
     * Returns the label for a given option object
     *
     * @param object $object
     * @param string $dataLocale
     *
     * @return string
     */
    public function getOptionLabel($object, $dataLocale);

    /**
     * Returns the id for a given option object
     *
     * @param object $object
     * @param string $dataLocale
     *
     * @return string
     */
    public function getOptionId($object);
}
