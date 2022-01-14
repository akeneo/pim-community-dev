import React from 'react';
import {filterErrors, useTranslate} from '@akeneo-pim-community/shared';
import {Filter} from './models';
import {SectionTitle} from 'akeneo-design-system';
import {useValidationErrors} from './contexts';
import {
  CategoryFilter,
  CategoryFilterType,
  createDefaultCagoriesFilter,
} from './components/CategorySelector/CategoryFilter';
import {EnabledFilter, EnabledFilterType} from './components/EnabledFilter/EnabledFilter';
import {CompletenessFilter, CompletenessFilterType, createDefaultCompletenessFilter} from '.';

type FilterConfiguratorProps = {
  filters: Filter[];
  onFiltersConfigurationChange: (filters: Filter[]) => void;
};

const FilterConfigurator = ({filters, onFiltersConfigurationChange}: FilterConfiguratorProps) => {
  const translate = useTranslate();
  const validationErrors = useValidationErrors(`[filters]`, false);

  return (
    <>
      <SectionTitle>
        <SectionTitle.Title>{translate('akeneo.syndication.filters.completeness.label')}</SectionTitle.Title>
      </SectionTitle>
      <CompletenessFilter
        availableOperators={[
          'ALL',
          'GREATER OR EQUALS THAN ON AT LEAST ONE LOCALE',
          'GREATER OR EQUALS THAN ON ALL LOCALES',
          'LOWER THAN ON ALL LOCALES',
        ]}
        filter={
          (filters.find(filter => filter.field === 'completeness') as CompletenessFilterType) ??
          createDefaultCompletenessFilter()
        }
        onChange={(updatedFilter: Filter) => {
          onFiltersConfigurationChange([...filters.filter(filter => filter.field !== 'completeness'), updatedFilter]);
        }}
        validationErrors={filterErrors(validationErrors, '[completeness]')}
      />
      <SectionTitle>
        <SectionTitle.Title>{translate('akeneo.syndication.filters.categories.label')}</SectionTitle.Title>
      </SectionTitle>
      <CategoryFilter
        filter={
          (filters.find(filter => filter.field === 'categories') as CategoryFilterType) ?? createDefaultCagoriesFilter()
        }
        onChange={(updatedFilter: Filter) => {
          onFiltersConfigurationChange([...filters.filter(filter => filter.field !== 'categories'), updatedFilter]);
        }}
        validationErrors={filterErrors(validationErrors, '[categories]')}
      />
      <SectionTitle>
        <SectionTitle.Title>{translate('akeneo.syndication.filters.enabled.label')}</SectionTitle.Title>
      </SectionTitle>
      <EnabledFilter
        filter={(filters.find(filter => filter.field === 'enabled') as EnabledFilterType) ?? undefined}
        onChange={(updatedFilter: Filter | undefined) => {
          const previousFilters = filters.filter(filter => filter.field !== 'enabled');

          if (undefined === updatedFilter) {
            onFiltersConfigurationChange(previousFilters);
            return;
          }

          onFiltersConfigurationChange([...previousFilters, updatedFilter as EnabledFilterType]);
        }}
        validationErrors={filterErrors(validationErrors, '[enabled]')}
      />
    </>
  );
};

export {FilterConfigurator};
