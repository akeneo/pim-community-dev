<?php
namespace Pim\Bundle\CatalogBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
/**
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright Copyright (c) 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GroupType extends AbstractType
{
    
    protected $group = null;
    
    protected $values = null;
    
    protected $idProductType;
    
    /**
     * Constructor for group type
     * 
     * @param unknown_type $group
     * @param unknown_type $values
     */
    public function __construct($idProductType, $group = null, $values = null)
    {
        $this->idProductType = $idProductType; // useless ?
        if ($group) {
            $this->group = $group;
        }
        if ($values) {
            $this->values = $values;
        }
    }
    
    /**
     * (non-PHPdoc)
     * @see Symfony\Component\Form.AbstractType::buildForm()
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('code');
        $builder->add('idProductType', 'hidden');
    }

    /**
     * (non-PHPdoc)
     * @see \Symfony\Component\Form\FormTypeInterface::getName()
     */
    public function getName()
    {
        return 'akeneo_producttype_group';
    }
    
    /**
     * (non-PHPdoc)
     * @see \Symfony\Component\Form\AbstractType::getDefaultOptions()
     */
    public function getDefaultOptions(array $options)
    {
        return array('csrf_protection' => false);
    }
}