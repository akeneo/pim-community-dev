<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2017 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\ActivityManager\Job\Import;

use Pim\Component\Connector\ArrayConverter\ArrayConverterInterface;
use Pim\Component\Connector\ArrayConverter\FieldsRequirementChecker;

/**
 * Convert flat project to standard format.
 *
 * @author Arnaud Langlade <arnaud.langlade@akeneo.com>
 */
class ProjectArrayConverter implements ArrayConverterInterface
{
    /** @var FieldsRequirementChecker */
    protected $fieldsRequirementChecker;

    /**
     * @param FieldsRequirementChecker $fieldsRequirementChecker
     */
    public function __construct(FieldsRequirementChecker $fieldsRequirementChecker)
    {
        $this->fieldsRequirementChecker = $fieldsRequirementChecker;
    }

    /**
     * {@inheritdoc}
     */
    public function convert(array $projectData, array $options = [])
    {
        $mandatoriesField = ['owner', 'label', 'locale', 'channel', 'datagrid_view-columns', 'datagrid_view-filters'];

        $this->fieldsRequirementChecker->checkFieldsPresence($projectData, $mandatoriesField);
        $this->fieldsRequirementChecker->checkFieldsFilling($projectData, $mandatoriesField);

        $convertedProject = [];
        foreach ($projectData as $field => $value) {
            if ('' !== $value) {
                $convertedProject = $this->convertField($convertedProject, $field, $value);
            }
        }

        return $convertedProject;
    }

    /**
     * Convert a field
     *
     * @param array  $convertedProject
     * @param string $field
     * @param mixed  $value
     *
     * @return array
     */
    protected function convertField($convertedProject, $field, $value)
    {
        if (false !== strpos($field, 'datagrid_view-', 0)) {
            list($prefix, $datagridViewColumn) = explode('-', $field);
            $convertedProject['datagrid_view'][$datagridViewColumn] = $value;
        } elseif ('product_filters' === $field) {
            $convertedProject[$field] = unserialize($value);
        } else {
            $convertedProject[$field] = $value;
        }

        return $convertedProject;
    }
}
