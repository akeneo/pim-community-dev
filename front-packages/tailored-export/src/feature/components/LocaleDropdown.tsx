import React, {ReactElement} from 'react';
import {Field, Helper, Locale, SelectInput, HelperProps} from 'akeneo-design-system';
import {
  useTranslate,
  Locale as LocaleModel,
  LocaleCode,
  ChannelReference,
  ValidationError,
} from '@akeneo-pim-community/shared';

type LocaleDropdownProps = {
  value: LocaleCode;
  locales: LocaleModel[];
  validationErrors: ValidationError[];
  channel?: ChannelReference;
  onChange: (updatedValue: LocaleCode) => void;
  children?: ReactElement<HelperProps> | null | false;
};

const LocaleDropdown = ({value, locales, onChange, validationErrors, children = null}: LocaleDropdownProps) => {
  const translate = useTranslate();

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
      {children}
      {validationErrors.map((error, index) => (
        <Helper key={index} inline={true} level="error">
          {translate(error.messageTemplate, error.parameters)}
        </Helper>
      ))}
    </Field>
  );
};

export {LocaleDropdown};
