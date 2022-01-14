import React from 'react';
import {AssetCollectionMainMediaUrlSelection, AssetCollectionSelection} from './model';
import {ChannelDropdown} from '../../shared/ChannelDropdown';
import {
  ChannelCode,
  filterErrors,
  getLocaleFromChannel,
  getLocalesFromChannel,
  ValidationError,
} from '@akeneo-pim-community/shared';
import {LocaleDropdown} from '../../shared/LocaleDropdown';
import {useChannels} from '../../../hooks';
import {NumberInput} from 'akeneo-design-system';

type AssetCollectionMainMediaUrlSelectorProps = {
  selection: AssetCollectionMainMediaUrlSelection;
  validationErrors: ValidationError[];
  onSelectionChange: (updatedSelection: AssetCollectionSelection) => void;
};

const AssetCollectionMainMediaUrlSelector = ({
  selection,
  validationErrors,
  onSelectionChange,
}: AssetCollectionMainMediaUrlSelectorProps) => {
  const channelErrors = filterErrors(validationErrors, '[channel]');
  const localeErrors = filterErrors(validationErrors, '[locale]');

  const channels = useChannels();
  const locales = getLocalesFromChannel(channels, selection.channel);

  return (
    <>
      {null !== selection.channel && (
        <ChannelDropdown
          value={selection.channel}
          channels={channels}
          validationErrors={channelErrors}
          onChange={(channelCode: ChannelCode) => {
            const localeCode = getLocaleFromChannel(channels, channelCode, selection.locale);
            onSelectionChange({...selection, locale: localeCode, channel: channelCode});
          }}
        />
      )}
      {null !== selection.locale && (
        <LocaleDropdown
          value={selection.locale}
          validationErrors={localeErrors}
          locales={locales}
          onChange={localeCode => onSelectionChange({...selection, locale: localeCode})}
        />
      )}
      <NumberInput
        value={String(selection.position)}
        onChange={newPosition => onSelectionChange({...selection, position: parseInt(newPosition)})}
      />
    </>
  );
};

export {AssetCollectionMainMediaUrlSelector};
