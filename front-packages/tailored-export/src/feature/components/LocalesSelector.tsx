import React from 'react';
import {useTranslate, LocaleCode, Section, Locale, ValidationError} from '@akeneo-pim-community/shared';
import {Field, MultiSelectInput, Helper} from 'akeneo-design-system';

type LocalesSelectorProps = {
  label: string;
  placeholder: string;
  removeLabel: string;
  value: LocaleCode[];
  locales: Locale[];
  onChange: (newLocales: LocaleCode[]) => void;
  validationErrors: ValidationError[];
};

const LocalesSelector = ({
  locales,
  value,
  onChange,
  validationErrors,
  removeLabel,
  placeholder,
  label,
}: LocalesSelectorProps) => {
  const translate = useTranslate();

  return (
    <Section>
      <Field label={label}>
        <MultiSelectInput
          emptyResultLabel={translate('pim_common.no_result')}
          onChange={onChange}
          openLabel={translate('pim_common.open')}
          placeholder={placeholder}
          removeLabel={removeLabel}
          value={value}
        >
          {locales.map((locale: Locale) => (
            <MultiSelectInput.Option key={locale.code} value={locale.code}>
              {locale.label}
            </MultiSelectInput.Option>
          ))}
        </MultiSelectInput>
        {validationErrors.map((error, index) => (
          <Helper key={index} inline={true} level="error">
            {translate(error.messageTemplate, error.parameters)}
          </Helper>
        ))}
      </Field>
    </Section>
  );
};

export {LocalesSelector};
