import {getAllLocalesFromChannels, useTranslate} from '@akeneo-pim-community/shared';
import {Field, SelectInput} from 'akeneo-design-system';
import {Selection} from '../../../../models';
import React from 'react';
import {useChannels} from '../../../../hooks';
import {LocaleDropdown} from '../LocaleDropdown';

type CodeLabelSelectorProps = {
  selection: Selection;
  onSelectionChange: (updatedSelection: Selection) => void;
};

const CodeLabelSelector = ({selection, onSelectionChange}: CodeLabelSelectorProps) => {
  const translate = useTranslate();
  const channels = useChannels();
  const locales = getAllLocalesFromChannels(channels);

  return (
    <>
      <Field label={translate('pim_common.type')}>
        <SelectInput
          clearable={false}
          emptyResultLabel={translate('pim_common.no_result')}
          openLabel={translate('pim_common.open')}
          value={selection.type}
          onChange={type => {
            if ('label' === type) {
              onSelectionChange({type, locale: locales[0].code});
            } else if ('code' === type) {
              onSelectionChange({type});
            }
          }}
        >
          <SelectInput.Option title={translate('pim_common.label')} value="label">
            {translate('pim_common.label')}
          </SelectInput.Option>
          <SelectInput.Option title={translate('pim_common.code')} value="code">
            {translate('pim_common.code')}
          </SelectInput.Option>
        </SelectInput>
      </Field>
      {'label' === selection.type && (
        <LocaleDropdown
          value={selection.locale}
          onChange={updatedValue => onSelectionChange({...selection, locale: updatedValue})}
        />
      )}
    </>
  );
};

export {CodeLabelSelector};
