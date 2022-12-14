import React from 'react';
import {Channel, ChannelCode, getLabel, useTranslate, useUserContext} from '@akeneo-pim-community/shared';
import {Dropdown, SwitcherButton, useBooleanState} from 'akeneo-design-system';

type ChannelSelectorProps = {
  value: ChannelCode;
  values: Channel[];
  onChange: (value: ChannelCode) => void;
};
const ChannelSelector = ({value, values, onChange}: ChannelSelectorProps) => {
  const translate = useTranslate();
  const userContext = useUserContext();
  const catalogLocale = userContext.get('catalogLocale');
  const [isOpen, open, close] = useBooleanState(false);
  const selectedChannel: Channel | undefined = values.find(channel => channel.code === value);
  const handleChange = (channelCode: ChannelCode) => onChange?.(channelCode);

  return (
    <Dropdown>
      <SwitcherButton
        inline
        onClick={() => {
          open();
        }}
        label={translate('pim_common.channel')}
      >
        <span data-testid={`ChannelSelector.selection`}>
          {selectedChannel && getLabel(selectedChannel.labels, catalogLocale, `${selectedChannel.code}`)}
        </span>
      </SwitcherButton>
      {isOpen && (
        <Dropdown.Overlay verticalPosition="down" onClose={close}>
          <Dropdown.Header>
            <Dropdown.Title>{translate('pim_common.channel')}</Dropdown.Title>
          </Dropdown.Header>
          <Dropdown.ItemCollection>
            {values.map(channel => {
              return (
                <Dropdown.Item
                  aria-selected={channel.code === value}
                  key={channel.code}
                  onClick={() => {
                    close();
                    handleChange(channel.code);
                  }}
                  isActive={channel.code === selectedChannel?.code}
                >
                  <span key={channel.code}>{getLabel(channel.labels, catalogLocale, `${channel.code}`)}</span>
                </Dropdown.Item>
              );
            })}
          </Dropdown.ItemCollection>
        </Dropdown.Overlay>
      )}
    </Dropdown>
  );
};

export {ChannelSelector};
