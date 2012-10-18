<?php
namespace Strixos\IcecatConnectorBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;

use Symfony\Component\Form\AbstractType;

/**
 * 
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright Copyright (c) 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class CollectionType extends AbstractType
{
    /**
     * (non-PHPdoc)
     * @see \Symfony\Component\Form\AbstractType::buildForm()
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $entities = $options['data'];
        
        foreach ($entities as $entity)
        {
        	$builder->add(
        		'configs', 'collection',
        		array(
        			'type' 		   => new ConfigType($builder, $entity),
        			'allow_add'	   => true,
        			'allow_delete' => true,
        			'by_reference' => false,
        		)
        	);
        }
    }
    
    /**
     * (non-PHPdoc)
     * @see \Symfony\Component\Form\FormTypeInterface::getName()
     */
    public function getName()
    {
        return 'strixos_icecatconnector_config';
    }
}