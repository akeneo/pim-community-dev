<?php
namespace Akeneo\CatalogBundle\Doctrine;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Entity type manager, a general doctrine implementation, not depends on storage (entity or document)
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright Copyright (c) 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class EntityTypeManager extends AbstractManager
{

    /**
     * List of groups codes
     * @var Array
     */
    protected $_codeToGroup;

    /**
     * List of fields codes
     * @var Array
     */
    protected $_codeToField;

    /**
     * Get groups code
     * @return Array
     */
    public function getGroupsCodes()
    {
        return array_keys($this->_codeToGroup);
    }

    /**
     * Get fields code
     * @return Array
     */
    public function getFieldsCodes()
    {
        return array_keys($this->_codeToField);
    }

}