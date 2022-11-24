import {Channel, ChannelCode, useTranslate, useUserContext} from '@akeneo-pim-community/shared';
import {
  Dropdown,
  SwitcherButton,
  useBooleanState
} from 'akeneo-design-system';
import React, {forwardRef, Ref} from 'react';

const ChannelSpan = forwardRef<HTMLSpanElement, ChannelProps>(
    ({code, label, ...rest}: ChannelProps, forwardedRef: Ref<HTMLSpanElement>) => {
      return (
          <span ref={forwardedRef} {...rest}>
          {label || `[${code}]`}
        </span>
      );
    }
);

type ChannelProps = {
  code: string;
  label: string;
};

type ChannelSelectorProps = {
  value: ChannelCode;
  values: Channel[];
  onChange: (value: ChannelCode) => void;
}
const ChannelSelector = ({value, values, onChange}: ChannelSelectorProps) => {
  const translate = useTranslate();
  const userContext = useUserContext();
  const catalogLocale = userContext.get('catalogLocale');
  const [isOpen, open, close] = useBooleanState(false);
  const selectedChannel: Channel = values.find(channel => channel.code === value) || values[0];

  const handleChange = (channelCode: ChannelCode) => onChange?.(channelCode);

  return (
    <Dropdown>
      <SwitcherButton inline onClick={() => {open();}} label={translate('pim_common.channel')}>
        <ChannelSpan code={`[${selectedChannel?.code}]`} label={selectedChannel?.labels[catalogLocale]} />
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
                    <ChannelSpan
                      code={channel.code}
                      label={channel.labels[catalogLocale]}
                      key={channel.code}
                    />
                  </Dropdown.Item>
                );
              }
            )}
          </Dropdown.ItemCollection>
        </Dropdown.Overlay>
      )}
    </Dropdown>
  );
}

export {ChannelSelector};
