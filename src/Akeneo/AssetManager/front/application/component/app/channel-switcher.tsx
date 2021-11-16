import React from 'react';
import {Dropdown, SwitcherButton, useBooleanState} from 'akeneo-design-system';
import {Channel, ChannelCode, getChannelLabel, useTranslate} from '@akeneo-pim-community/shared';

type ChannelSwitcherProps = {
  channelCode: string;
  channels: Channel[];
  locale: string;
  onChannelChange: (channelCode: ChannelCode) => void;
};

const ChannelSwitcher = ({channelCode, channels, locale, onChannelChange}: ChannelSwitcherProps) => {
  const translate = useTranslate();
  const [isDropdownOpen, openDropdown, closeDropdown] = useBooleanState();
  const channel = channels.find(({code}) => code === channelCode) ?? channels[0];

  const handleChange = (channelCode: ChannelCode) => () => {
    closeDropdown();
    onChannelChange(channelCode);
  };

  return (
    <Dropdown>
      <SwitcherButton label={translate('pim_common.channel')} onClick={openDropdown}>
        {getChannelLabel(channel, locale)}
      </SwitcherButton>
      {isDropdownOpen && (
        <Dropdown.Overlay onClose={closeDropdown}>
          <Dropdown.Header>
            <Dropdown.Title>{translate('pim_common.channel')}</Dropdown.Title>
          </Dropdown.Header>
          <Dropdown.ItemCollection>
            {channels.map(channel => (
              <Dropdown.Item
                key={channel.code}
                onClick={handleChange(channel.code)}
                isActive={channel.code === channelCode}
              >
                {getChannelLabel(channel, locale)}
              </Dropdown.Item>
            ))}
          </Dropdown.ItemCollection>
        </Dropdown.Overlay>
      )}
    </Dropdown>
  );
};

export {ChannelSwitcher};
