<?php

namespace Oro\Bundle\SearchBundle\Extension;

use Oro\Bundle\DataGridBundle\Datasource\ResultRecordInterface;
use Oro\Bundle\DataGridBundle\Extension\Formatter\Property\PropertyConfiguration;
use Oro\Bundle\DataGridBundle\Extension\Formatter\Property\TwigTemplateProperty;
use Symfony\Component\Security\Core\Util\ClassUtils;

class SearchResultProperty extends TwigTemplateProperty
{
    /**
     * {@inheritdoc}
     */
    public function getValue(ResultRecordInterface $record)
    {
        $entity = $record->getValue('entity');
        $entityClass = ClassUtils::getRealClass($entity);
        $bundleName = explode('\\', $entityClass)[2];
        $templateName = 'Oro'.$bundleName.':Search/result.html.twig';

        /** @var PropertyConfiguration $params */
        $params = $this->params;
        $template = $this->params->offsetGetOr('template', false);
        if (!$template) {
            $this->params->offsetSet('template', $templateName);
        }

        return $this->getTemplate()->render(
            array(
                'indexer_item' => $record->getValue('indexer_item'),
                'entity'       => $record->getValue('entity'),
            )
        );
    }
}
