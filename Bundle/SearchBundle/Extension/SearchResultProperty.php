<?php

namespace Oro\Bundle\SearchBundle\Extension;

use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Security\Core\Util\ClassUtils;

use Oro\Bundle\DataGridBundle\Datasource\ResultRecordInterface;
use Oro\Bundle\DataGridBundle\Extension\Formatter\Property\TwigTemplateProperty;

class SearchResultProperty extends TwigTemplateProperty
{
    /** @var array */
    protected $entitiesConfig;

    public function __construct(
        \Twig_Environment $environment,
        $config
    ) {
        parent::__construct($environment);

        $this->entitiesConfig = $config;
    }

    /**
     * {@inheritdoc}
     */
    public function getValue(ResultRecordInterface $record)
    {
        $entity = $record->getValue('entity');
        $entityClass = ClassUtils::getRealClass($entity);

        if (empty($this->entitiesConfig[$entityClass])) {
            throw new InvalidConfigurationException(
                sprintf('Unknown entity type %s, unable to find search configuration', $entityClass)
            );
        } else {
            $searchTemplate = $this->entitiesConfig[$entityClass]['search_template'];
        }

        $template = $this->params->offsetGetOr('template', false);
        if (!$template) {
            $this->params->offsetSet('template', $searchTemplate);
        }

        return $this->getTemplate()->render(
            array(
                'indexer_item' => $record->getValue('indexer_item'),
                'entity'       => $record->getValue('entity'),
            )
        );
    }

    /**
     * @param array $configArray
     */
    public function setEntitiesConfig(array $configArray)
    {
        $this->entitiesConfig = $configArray;
    }
}
