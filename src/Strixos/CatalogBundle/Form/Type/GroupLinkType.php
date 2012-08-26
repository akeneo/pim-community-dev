<?php
namespace Strixos\CatalogBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Strixos\CatalogBundle\Entity\Group;

/**
 * Aims to use collection of groups link to pick them in set form
 *
 * @author     Nicolas Dupont @ Strixos
 * @copyright  Copyright (c) 2012 Strixos SAS (http://www.strixos.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class GroupLinkType extends AbstractType
{
    /**
     * Used to populate from the constructor
     * @param Group
     */
    private $_group = null;

    /**
     * Construct
     */
    public function __construct($group = null)
    {
        if ($group) {
            $this->_group = $group;
        }
    }

    /**
     * (non-PHPdoc)
     * @see Symfony\Component\Form.AbstractType::buildForm()
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('id', 'hidden');
        $builder->add('code');
        if (!is_null($this->_group)) {
            $builder->setData($this->_group);
        }
        // group attributes
        $builder->add(
            'attributes', 'collection',
            array(
                'type'         => new AttributeLinkType(),
                'by_reference' => false,
            )
        );
    }

    /**
     * (non-PHPdoc)
     * @see Symfony\Component\Form.AbstractType::getDefaultOptions()
     */
    public function getDefaultOptions(array $options)
    {
        return array(
            'data_class' => 'Strixos\CatalogBundle\Entity\Group',
        );
    }

    /**
     * Return identifier
     * @see Symfony\Component\Form.FormTypeInterface::getName()
     */
    public function getName()
    {
        return 'strixos_catalog_group_link';
    }

}