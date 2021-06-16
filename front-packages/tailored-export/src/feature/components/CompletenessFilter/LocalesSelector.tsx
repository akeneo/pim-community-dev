import React from 'react';
import {useTranslate, LocaleCode, Section, getAllLocalesFromChannels, Locale} from '@akeneo-pim-community/shared';
import {Field, MultiSelectInput} from 'akeneo-design-system';
import {useChannels} from '../../hooks';

type LocalesSelectorProps = {
  locales: LocaleCode[];
  onChange: (newLocales: LocaleCode[]) => void;
};
const LocalesSelector = ({locales, onChange}: LocalesSelectorProps) => {
  const translate = useTranslate();
  const channels = useChannels();
  const availableLocales = getAllLocalesFromChannels(channels);

  if (availableLocales.length === 0) return null;

  return (
    <Section>
      <Field label={translate('akeneo.tailored_export.filters.completeness.locales.label')}>
        <MultiSelectInput
          emptyResultLabel={translate('pim_common.no_result')}
          onChange={onChange}
          openLabel={translate('pim_common.open')}
          placeholder={translate('akeneo.tailored_export.filters.completeness.locales.placeholder')}
          removeLabel={translate('akeneo.tailored_export.filters.completeness.locales.remove')}
          value={locales}
        >
          {availableLocales.map((locale: Locale) => (
            <MultiSelectInput.Option key={locale.code} value={locale.code}>
              {locale.label}
            </MultiSelectInput.Option>
          ))}
        </MultiSelectInput>
      </Field>
    </Section>
  );
};

export {LocalesSelector};
