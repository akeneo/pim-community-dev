import React, {useEffect, useState} from 'react';
import {MultiSelectInput} from 'akeneo-design-system';
import {useTranslate, userContext} from '@akeneo-pim-community/shared';
import {Channel, ChannelCode, getChannelLabel} from '../models';
import {useFetchers} from '../contexts';

type Props = {
  onChange: (value: ChannelCode[]) => void;
};

const MultiChannelInput = ({onChange}: Props) => {
  const translate = useTranslate();
  const fetcher = useFetchers();
  const labels = {};
  labels[userContext.get('catalogLocale')] = translate(
    'akeneo.performance_analytics.control_panel.multi_input.all_channels'
  );
  const [channels, setChannels] = useState<Channel[]>([{code: '<all_channels>', labels: labels}]);
  const [values, setValues] = useState<ChannelCode[]>(['<all_channels>']);

  useEffect(() => {
    const fetchChannels = async () => {
      return await fetcher.channel.fetchChannels();
    };

    fetchChannels().then(async newChannels => {
      setChannels([...channels, ...newChannels]);
    });
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [fetcher.channel]);

  const handleChange = (newValues: ChannelCode[]) => {
    if (newValues.length === 0 || (!values.includes('<all_channels>') && newValues.includes('<all_channels>'))) {
      newValues = ['<all_channels>'];
    }

    if (values.includes('<all_channels>') && newValues.length > 1) {
      newValues = newValues.filter(value => value !== '<all_channels>');
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
      {channels.map((channels: Channel) => (
        <MultiSelectInput.Option value={channels.code} key={channels.code}>
          {getChannelLabel(channels, userContext.get('catalogLocale'))}
        </MultiSelectInput.Option>
      ))}
    </MultiSelectInput>
  );
};

export {MultiChannelInput};
