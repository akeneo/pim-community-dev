import {
  getLocalesFromChannel,
  useTranslate,
  Locale as LocaleModel,
  LocaleCode,
  ChannelReference,
} from '@akeneo-pim-community/shared';
import {Field, Locale, SelectInput} from 'akeneo-design-system';
import {useChannels} from '../../../hooks';
import React from 'react';

type LocaleDropdownProps = {
  value: LocaleCode;
  channel?: ChannelReference;
  onChange: (updatedValue: LocaleCode) => void;
};

const LocaleDropdown = ({value, channel = null, onChange}: LocaleDropdownProps) => {
  const translate = useTranslate();
  const channels = useChannels();
  const locales = getLocalesFromChannel(channels, channel);

  return (
    <Field label={translate('pim_common.locale')}>
      <SelectInput
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
    </Field>
  );
};

export {LocaleDropdown};
