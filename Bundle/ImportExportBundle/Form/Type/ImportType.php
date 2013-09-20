<?php

namespace Oro\Bundle\ImportExportBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Oro\Bundle\ImportExportBundle\Processor\ProcessorRegistry;

class ImportType extends AbstractType
{
    const NAME = 'oro_importexport_import';

    /**
     * @var ProcessorRegistry
     */
    protected $processorRegistry;

    /**
     * @param ProcessorRegistry $processorRegistry
     */
    public function __construct(ProcessorRegistry $processorRegistry)
    {
        $this->processorRegistry = $processorRegistry;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'file',
            'file',
            array(
                'required' => true
            )
        );

        $processorChoices = $this->getImportProcessorsChoices($options['entityName']);

        $builder->add(
            'processor',
            'choice',
            array(
                'choices' => $processorChoices,
                'required' => true,
                'preferred_choices' => $processorChoices ? array(reset($processorChoices)) : array(),
            )
        );
    }

    protected function getImportProcessorsChoices($entityName)
    {
        $aliases = $this->processorRegistry->getProcessorAliasesByEntity(
            ProcessorRegistry::TYPE_IMPORT,
            $entityName
        );
        $result = array();
        foreach ($aliases as $alias) {
            $result[$alias] = $this->generateProcessorLabel($alias);
        }
        return $result;
    }

    protected function generateProcessorLabel($alias)
    {
        // @TODO Remove hardcode of label parts
        return sprintf('oro_importexport.import.entity_import_from_csv.%s', $alias);
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setRequired(array('entityName'));
        $resolver->setAllowedTypes(
            array(
                'entityName' => 'string'
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::NAME;
    }
}
