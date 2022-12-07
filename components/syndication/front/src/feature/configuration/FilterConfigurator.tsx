import React, {useMemo, useCallback} from 'react';
import {useValidationErrors} from './contexts';

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

  const criteria = useMemo(
    () =>
      Object.fromEntries(
        filters.map((filter: Filter) => [
          filter.uuid ?? generateRandomId(),
          {...filter, locale: filter?.context?.locale ?? null, scope: filter?.context?.scope ?? null},
        ])
      ),
    [filters]
  );

  const handleCriteriaChange = useCallback(
    (criteria: ProductSelectionValues) => {
      const updatedFilters = Object.keys(criteria).map((filterKey: string) => {
        const filter = criteria[filterKey];

        return {
          field: filter.field,
          uuid: filterKey,
          operator: filter.operator,
          value: filter.value,
          context: {
            locale: filter.locale ?? null,
            channel: filter.scope ?? null,
            scope: filter.scope ?? null,
          },
        };
      });

      if (JSON.stringify(updatedFilters) !== JSON.stringify(filters)) {
        onFiltersConfigurationChange(updatedFilters);
      }
    },
    [onFiltersConfigurationChange, filters]
  );

  return <ProductSelection key={key} criteria={criteria} onChange={handleCriteriaChange} errors={validationErrors} />;
};

export {FilterConfigurator};
