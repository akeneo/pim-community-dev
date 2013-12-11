<?php

namespace Pim\Bundle\UIBundle\Entity\Repository;

/**
 * Interface for option repositories.
 *
 * Should be applied on the repositories of all entities used in AJAX choice fields.
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
     * The returned format must be the one expected by select2 :
     *
     *  return array(
     *      'results => array(
     *          array('id' => 1, 'text' => 'Choice 1'),
     *          array('id' => 2, 'text' => 'Choice 2'),
     *      )
     *  );
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
