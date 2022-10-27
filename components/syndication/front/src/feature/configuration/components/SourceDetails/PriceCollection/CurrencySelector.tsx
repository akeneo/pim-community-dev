import React from 'react';
import {Field, Helper, SelectInput} from 'akeneo-design-system';
import {
  ChannelReference,
  getCurrencyCodesFromChannelReference,
  useTranslate,
  ValidationError,
} from '@akeneo-pim-community/shared';
import {useChannels} from '../../../hooks';

type CurrencySelectorProps = {
  value: string;
  onChange: (newCurrencies: string) => void;
  channelReference: ChannelReference;
  validationErrors: ValidationError[];
};

const CurrencySelector = ({value, onChange, channelReference, validationErrors}: CurrencySelectorProps) => {
  const translate = useTranslate();
  const channels = useChannels();
  const currencyCodes = getCurrencyCodesFromChannelReference(channels, channelReference);

  return (
    <Field label={translate('akeneo.syndication.data_mapping_details.sources.selection.price.currency')}>
      <SelectInput
        value={value}
        onChange={onChange}
        emptyResultLabel={translate('pim_common.no_result')}
        openLabel={translate('pim_common.open')}
        invalid={0 < validationErrors.length}
        clearable={false}
      >
        {currencyCodes.map((currencyCode: string) => (
          <SelectInput.Option key={currencyCode} value={currencyCode}>
            {currencyCode}
          </SelectInput.Option>
        ))}
      </SelectInput>
      {validationErrors.map((error, index) => (
        <Helper key={index} inline={true} level="error">
          {translate(error.messageTemplate, error.parameters, error.plural)}
        </Helper>
      ))}
    </Field>
  );
};

export {CurrencySelector};
