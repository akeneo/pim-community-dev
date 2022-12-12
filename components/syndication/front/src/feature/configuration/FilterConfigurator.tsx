import React, {useCallback} from 'react';
import {useValidationErrors} from './contexts';

import {ValidationError} from '@akeneo-pim-community/shared';
import {ProductSelection, ProductSelectionValues} from '@akeneo-pim-community/catalogs';
import {Filter} from '../configuration/models';

export const generateRandomId = (): string => (Math.random() + 1).toString(36).substring(7);

type FilterConfiguratorProps = {
  key: string;
  filters: Filter[];
  onFiltersConfigurationChange: (filters: Filter[]) => void;
};

const FilterConfigurator = ({key, filters, onFiltersConfigurationChange}: FilterConfiguratorProps) => {
  const validationErrors = useValidationErrors(`[filters]`, false);

  const errors = validationErrors.reduce((accumulator: Record<string, string[]>, error: ValidationError) => {
    const extractRegex = /^\[([a-z0-9]*)\]/;
    const propertyId = extractRegex.exec(error.propertyPath);

    if (null === propertyId) {
      return accumulator;
    }

    if ('string' !== typeof propertyId[1]) {
      return accumulator;
    }

    const existingErrors = accumulator[propertyId[1]] || [];

    return {[propertyId[1]]: [...existingErrors, error.message]};
  }, {});

  const handleCriteriaChange = useCallback(
    (criteria: ProductSelectionValues) => {
      if (JSON.stringify(criteria) !== JSON.stringify(filters)) {
        onFiltersConfigurationChange(criteria);
      }
    },
    [onFiltersConfigurationChange, filters]
  );

  return <ProductSelection key={key} criteria={filters} onChange={handleCriteriaChange} errors={errors} />;
};

export {FilterConfigurator};
