import React from 'react';
import {useTranslate, LocaleCode, Section, Locale, ValidationError} from '@akeneo-pim-community/shared';
import {Field, MultiSelectInput, Helper} from 'akeneo-design-system';

type LocalesSelectorProps = {
  value: LocaleCode[];
  locales: Locale[];
  onChange: (newLocales: LocaleCode[]) => void;
  validationErrors: ValidationError[];
};

// TODO: Put this locale selector in common with the one from completeness
//       Pass-in the the label as a prop.
const LocalesSelector = ({locales, value, onChange, validationErrors}: LocalesSelectorProps) => {
  const translate = useTranslate();

  return (
    <Section>
      <Field label={translate('akeneo.tailored_export.filters.quality_score.locales.label')}>
        <MultiSelectInput
          emptyResultLabel={translate('pim_common.no_result')}
          onChange={onChange}
          openLabel={translate('pim_common.open')}
          placeholder={translate('akeneo.tailored_export.filters.quality_score.locales.placeholder')}
          removeLabel={translate('akeneo.tailored_export.filters.quality_score.locales.remove')}
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
