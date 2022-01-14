import React from 'react';
import {Field, Helper, MultiSelectInput} from 'akeneo-design-system';
import {
  ChannelReference,
  getCurrencyCodesFromChannelReference,
  useTranslate,
  ValidationError,
} from '@akeneo-pim-community/shared';
import {useChannels} from '../../../hooks';

type CurrencySelectorProps = {
  value: string[];
  onChange: (newCurrencies: string[]) => void;
  channelReference: ChannelReference;
  validationErrors: ValidationError[];
};

const CurrenciesSelector = ({value, onChange, channelReference, validationErrors}: CurrencySelectorProps) => {
  const translate = useTranslate();
  const channels = useChannels();
  const currencyCodes = getCurrencyCodesFromChannelReference(channels, channelReference);

  return (
    <Field label={translate('akeneo.syndication.data_mapping_details.sources.selection.price.currencies')}>
      <MultiSelectInput
        value={value}
        placeholder={translate('akeneo.syndication.data_mapping_details.sources.selection.price.all_currencies')}
        onChange={onChange}
        emptyResultLabel={translate('pim_common.no_result')}
        openLabel={translate('pim_common.open')}
        removeLabel={translate('pim_common.remove')}
        invalid={0 < validationErrors.length}
      >
        {currencyCodes.map((currencyCode: string) => (
          <MultiSelectInput.Option key={currencyCode} value={currencyCode}>
            {currencyCode}
          </MultiSelectInput.Option>
        ))}
      </MultiSelectInput>
      {validationErrors.map((error, index) => (
        <Helper key={index} inline={true} level="error">
          {translate(error.messageTemplate, error.parameters, error.plural)}
        </Helper>
      ))}
    </Field>
  );
};

export {CurrenciesSelector};
