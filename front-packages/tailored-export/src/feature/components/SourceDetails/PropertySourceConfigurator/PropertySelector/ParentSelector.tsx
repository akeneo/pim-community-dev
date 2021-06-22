import React from 'react';
import {Field, Helper, SelectInput} from 'akeneo-design-system';
import {
  filterErrors,
  getAllLocalesFromChannels,
  Section,
  useTranslate,
  ValidationError,
} from '@akeneo-pim-community/shared';
import {ParentSelection} from '../../../../models';
import {useChannels} from '../../../../hooks';
import {LocaleDropdown} from '../../../LocaleDropdown';
import {ChannelDropdown} from '../../../ChannelDropdown';

type ParentSelectorProps = {
  selection: ParentSelection;
  validationErrors: ValidationError[];
  onSelectionChange: (updatedSelection: ParentSelection) => void;
};

const ParentSelector = ({selection, validationErrors, onSelectionChange}: ParentSelectorProps) => {
  const translate = useTranslate();
  const channels = useChannels();
  const locales = getAllLocalesFromChannels(channels);
  const localeErrors = filterErrors(validationErrors, '[locale]');
  const channelErrors = filterErrors(validationErrors, '[channel]');
  const typeErrors = filterErrors(validationErrors, '[type]');

  return (
    <Section>
      <Field label={translate('pim_common.type')}>
        <SelectInput
          clearable={false}
          emptyResultLabel={translate('pim_common.no_result')}
          openLabel={translate('pim_common.open')}
          value={selection.type}
          invalid={0 < typeErrors.length}
          onChange={type => {
            if ('label' === type) {
              onSelectionChange({type, channel: channels[0].code, locale: locales[0].code});
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
        {typeErrors.map((error, index) => (
          <Helper key={index} inline={true} level="error">
            {translate(error.messageTemplate, error.parameters)}
          </Helper>
        ))}
      </Field>
      {'label' === selection.type && (
        <>
          <ChannelDropdown
            channels={channels}
            value={selection.channel}
            validationErrors={channelErrors}
            onChange={updatedValue => onSelectionChange({...selection, channel: updatedValue})}
          >
            <Helper inline={true} level='info'>
              {translate('akeneo.tailored_export.column_details.sources.selection.parent.information.channel')}
            </Helper>
          </ChannelDropdown>
          <LocaleDropdown
            locales={locales}
            value={selection.locale}
            validationErrors={localeErrors}
            onChange={updatedValue => onSelectionChange({...selection, locale: updatedValue})}
          >
            <Helper inline={true} level='info'>
              {translate('akeneo.tailored_export.column_details.sources.selection.parent.information.locale')}
            </Helper>
          </LocaleDropdown>
        </>
      )}
    </Section>
  );
};

export {ParentSelector};
