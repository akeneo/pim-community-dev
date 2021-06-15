import {
  getLocalesFromChannel,
  useTranslate,
  Locale as LocaleModel,
  LocaleCode,
  ChannelReference,
  ValidationError,
} from '@akeneo-pim-community/shared';
import {Field, Helper, Locale, SelectInput} from 'akeneo-design-system';
import {useChannels} from '../../../hooks';
import React from 'react';

type LocaleDropdownProps = {
  value: LocaleCode;
  channel?: ChannelReference;
  validationErrors: ValidationError[];
  onChange: (updatedValue: LocaleCode) => void;
};

const LocaleDropdown = ({value, channel = null, validationErrors, onChange}: LocaleDropdownProps) => {
  const translate = useTranslate();
  const channels = useChannels();
  const locales = getLocalesFromChannel(channels, channel);

  return (
    <Field label={translate('pim_common.locale')}>
      <SelectInput
        invalid={0 < validationErrors.length}
        clearable={false}
        emptyResultLabel={translate('pim_common.no_result')}
        openLabel={translate('pim_common.open')}
        value={value}
        onChange={onChange}
      >
        {locales.map((locale: LocaleModel) => (
          <SelectInput.Option key={locale.code} title={locale.label} value={locale.code}>
            <Locale code={locale.code} languageLabel={locale.label} />
          </SelectInput.Option>
        ))}
      </SelectInput>
      {validationErrors.map((error, index) => (
        <Helper key={index} inline={true} level="error">
          {translate(error.messageTemplate, error.parameters)}
        </Helper>
      ))}
    </Field>
  );
};

export {LocaleDropdown};
