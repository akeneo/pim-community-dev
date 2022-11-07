import React, {useEffect, useState} from 'react';
import {MultiSelectInput} from 'akeneo-design-system';
import {useTranslate, userContext} from '@akeneo-pim-community/shared';
import {Locale, LocaleCode} from '../models';
import {useFetchers} from '../contexts';

type Props = {
  onChange: (value: LocaleCode[]) => void;
};

const MultiLocaleInput = ({onChange}: Props) => {
  const translate = useTranslate();
  const fetcher = useFetchers();
  const labels = {};
  labels[userContext.get('catalogLocale')] = translate(
    'akeneo.performance_analytics.control_panel.multi_input.all_locales'
  );
  const [locales, setLocales] = useState<Locale[]>([
    {
      code: '<all_locales>',
      label: translate('akeneo.performance_analytics.control_panel.multi_input.all_locales'),
    },
  ]);
  const [values, setValues] = useState<LocaleCode[]>(['<all_locales>']);

  useEffect(() => {
    const fetchActivatedLocales = async () => {
      return await fetcher.locale.fetchActivatedLocales();
    };

    fetchActivatedLocales().then(async newLocales => {
      setLocales([...locales, ...newLocales]);
    });
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [fetcher.locale]);

  const handleChange = (newValues: LocaleCode[]) => {
    if (newValues.length === 0 || (!values.includes('<all_locales>') && newValues.includes('<all_locales>'))) {
      newValues = ['<all_locales>'];
    }

    if (values.includes('<all_locales>') && newValues.length > 1) {
      newValues = newValues.filter(value => value !== '<all_locales>');
    }

    setValues(newValues);
    onChange(newValues);
  };

  return (
    <MultiSelectInput
      value={values}
      onChange={handleChange}
      emptyResultLabel={translate('pim_common.no_result')}
      openLabel={translate('pim_common.open')}
      removeLabel={translate('pim_common.remove')}
    >
      {locales.map((channels: Locale) => (
        <MultiSelectInput.Option value={channels.code} key={channels.code}>
          {channels.label}
        </MultiSelectInput.Option>
      ))}
    </MultiSelectInput>
  );
};

export {MultiLocaleInput};
