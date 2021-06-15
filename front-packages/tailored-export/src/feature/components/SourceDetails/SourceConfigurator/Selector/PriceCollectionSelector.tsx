import React from 'react';
import {Field, SelectInput} from 'akeneo-design-system';
import {Section, useTranslate} from '@akeneo-pim-community/shared';
import {PriceCollectionSelection} from '../../../../models';

type PriceCollectionSelectorProps = {
  selection: PriceCollectionSelection;
  onSelectionChange: (updatedSelection: PriceCollectionSelection) => void;
};

const PriceCollectionSelector = ({selection, onSelectionChange}: PriceCollectionSelectorProps) => {
  const translate = useTranslate();

  return (
    <Section>
      <Field label={translate('pim_common.type')}>
        <SelectInput
          clearable={false}
          emptyResultLabel={translate('pim_common.no_result')}
          openLabel={translate('pim_common.open')}
          value={selection.type}
          onChange={type => {
            if ('amount' === type || 'currency' === type) {
              onSelectionChange({type});
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
      </Field>
    </Section>
  );
};

export {PriceCollectionSelector};
