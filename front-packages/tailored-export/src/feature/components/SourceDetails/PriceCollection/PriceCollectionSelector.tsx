import React from 'react';
import {Field, Helper, SelectInput} from 'akeneo-design-system';
import {Section, filterErrors, useTranslate, ValidationError} from '@akeneo-pim-community/shared';
import {PriceCollectionSelection} from './model';

type PriceCollectionSelectorProps = {
  selection: PriceCollectionSelection;
  validationErrors: ValidationError[];
  onSelectionChange: (updatedSelection: PriceCollectionSelection) => void;
};

const PriceCollectionSelector = ({selection, validationErrors, onSelectionChange}: PriceCollectionSelectorProps) => {
  const translate = useTranslate();
  const typeErrors = filterErrors(validationErrors, '[type]');

  return (
    <Section>
      <Field label={translate('pim_common.type')}>
        <SelectInput
          clearable={false}
          invalid={0 < typeErrors.length}
          emptyResultLabel={translate('pim_common.no_result')}
          openLabel={translate('pim_common.open')}
          value={selection.type}
          onChange={type => {
            if ('amount' === type || 'currency' === type) {
              onSelectionChange({...selection, type});
            }
          }}
        >
          <SelectInput.Option
            title={translate('akeneo.tailored_export.column_details.sources.selection.type.amount')}
            value="amount"
          >
            {translate('akeneo.tailored_export.column_details.sources.selection.type.amount')}
          </SelectInput.Option>
          <SelectInput.Option
            title={translate('akeneo.tailored_export.column_details.sources.selection.type.currency')}
            value="currency"
          >
            {translate('akeneo.tailored_export.column_details.sources.selection.type.currency')}
          </SelectInput.Option>
        </SelectInput>
        <Helper inline={true} level="info">
          {translate('akeneo.tailored_export.column_details.sources.selection.price.information')}
        </Helper>
        {typeErrors.map((error, index) => (
          <Helper key={index} inline={true} level="error">
            {translate(error.messageTemplate, error.parameters)}
          </Helper>
        ))}
      </Field>
    </Section>
  );
};

export {PriceCollectionSelector};
