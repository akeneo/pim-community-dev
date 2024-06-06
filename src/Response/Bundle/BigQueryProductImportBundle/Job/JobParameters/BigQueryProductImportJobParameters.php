<?php

namespace Response\Bundle\BigQueryProductImportBundle\Job\JobParameters;

use Akeneo\Platform\Bundle\ImportExportBundle\Infrastructure\Validation\Storage;
use Akeneo\Tool\Component\Batch\Job\JobInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters\ConstraintCollectionProviderInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters\DefaultValuesProviderInterface;
use Akeneo\Tool\Component\Localization\Localizer\LocalizerInterface;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;

class BigQueryProductImportJobParameters implements
    DefaultValuesProviderInterface,
    ConstraintCollectionProviderInterface
{
    /** @var DefaultValuesProviderInterface */
    protected $simpleDefaultValueProvider;

    /** @var ConstraintCollectionProviderInterface */
    protected $simpleConstraintCollectionProvider;

    /** @var array */
    protected $supportedJobNames;

    /**
     * @param DefaultValuesProviderInterface $simpleDefaultValueProvider
     * @param ConstraintCollectionProviderInterface $simpleConstraintCollectionProvider
     * @param array                          $supportedJobNames
     */
    public function __construct(DefaultValuesProviderInterface $simpleDefaultValueProvider, 
        ConstraintCollectionProviderInterface $simpleConstraintCollectionProvider,
        array $supportedJobNames)
    {
        $this->simpleDefaultValueProvider = $simpleDefaultValueProvider;
        $this->simpleConstraintCollectionProvider = $simpleConstraintCollectionProvider;
        $this->supportedJobNames = $supportedJobNames;
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultValues()
    {
        $parameters = $this->simpleDefaultValueProvider->getDefaultValues();
        $parameters['bigquery_dataset'] = 'response-elt.prod_elt.denormalized_akeneo_catalog_item';
        $parameters['decimalSeparator'] = LocalizerInterface::DEFAULT_DECIMAL_SEPARATOR;
        $parameters['dateFormat'] = LocalizerInterface::DEFAULT_DATE_FORMAT;
        $parameters['enabled'] = true;
        $parameters['categoriesColumn'] = 'categories';
        $parameters['familyColumn'] = 'family';
        $parameters['groupsColumn'] = 'groups';
        $parameters['enabledComparison'] = true;
        $parameters['realTimeVersioning'] = true;
        $parameters['convertVariantToSimple'] = false;
        $parameters['invalid_items_file_format'] = 'bigquery';

        return $parameters;
    }

    /**
     * {@inheritdoc}
     */
    public function getConstraintCollection()
    {
        $baseConstraint = $this->simpleConstraintCollectionProvider->getConstraintCollection();
        $constraintFields = $baseConstraint->fields;
        $constraintFields['bigquery_dataset'] = new NotBlank();
        $constraintFields['decimalSeparator'] = new NotBlank();
        $constraintFields['dateFormat'] = new NotBlank();
        $constraintFields['enabled'] = new Type('bool');
        $constraintFields['categoriesColumn'] = new NotBlank();
        $constraintFields['familyColumn'] = new NotBlank();
        $constraintFields['groupsColumn'] = new NotBlank();
        $constraintFields['enabledComparison'] = new Type('bool');
        $constraintFields['realTimeVersioning'] = new Type('bool');
        $constraintFields['convertVariantToSimple'] = new Type('bool');
        $constraintFields['invalid_items_file_format'] = new NotBlank();

        return new Collection(['fields' => $constraintFields]);
    }

    /**
     * {@inheritdoc}
     */
    public function supports(JobInterface $job)
    {
        return in_array($job->getName(), $this->supportedJobNames);
    }
}
