import {ArrowDownIcon, Button, Dropdown, useBooleanState} from 'akeneo-design-system';
import Channel, {ChannelCode} from 'akeneoassetmanager/domain/model/channel';
import {LocaleCode} from 'akeneoassetmanager/domain/model/locale';
import {getLabel} from 'pimui/js/i18n';
import React from 'react';

type ChannelDropdownProps = {
  readOnly: boolean;
  channel: ChannelCode;
  uiLocale: LocaleCode;
  onChange: (newChannel: ChannelCode) => void;
  channels: Channel[];
};

const ChannelDropdown = ({
  readOnly,
  channel,
  uiLocale,
  onChange,
  channels,
}: ChannelDropdownProps) => {
  const [isOpen, open, close] = useBooleanState();
  const currentChannel = channels.find(channelItem => channelItem.code === channel);

  if (undefined === currentChannel) {
    return null;
  }

  return (
    <Dropdown>
      <Button disabled={readOnly} onClick={open}>
        {getLabel(currentChannel.labels, uiLocale, currentChannel.code)} <ArrowDownIcon />
      </Button>
      {isOpen && (
        <Dropdown.Overlay verticalPosition="down" onClose={close}>
          <Dropdown.Header>
            <Dropdown.Title>Channels</Dropdown.Title>
          </Dropdown.Header>
          <Dropdown.ItemCollection>
            {channels.map(currentChannel => (
              <Dropdown.Item
                key={currentChannel.code}
                onClick={() => {
                  onChange(currentChannel.code);
                  close();
                }}
              >
                {getLabel(currentChannel.labels, uiLocale, currentChannel.code)}
              </Dropdown.Item>
            ))}
          </Dropdown.ItemCollection>
        </Dropdown.Overlay>
      )}
    </Dropdown>
  );
};

export {ChannelDropdown};
