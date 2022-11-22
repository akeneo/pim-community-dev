import {Channel, ChannelCode, useTranslate} from '@akeneo-pim-community/shared';
import {
  AkeneoThemedProps,
  Dropdown,
  getColor,
  getFontSize,
  Pill,
  SwitcherButton,
  useBooleanState
} from 'akeneo-design-system';
import React, {forwardRef, Ref} from 'react';
import styled, {css} from "styled-components";

const DropdownContainer = styled(Dropdown)`
  text-transform: none;
  font-size: ${getFontSize('default')};
  color: ${getColor('grey', 120)};
`;

const ChannelSpan = forwardRef<HTMLSpanElement, ChannelProps>(
    ({code, label, ...rest}: ChannelProps, forwardedRef: Ref<HTMLSpanElement>) => {
      return (
          <span ref={forwardedRef} {...rest}>
          {label || code}
        </span>
      );
    }
);

const HighlightChannel = styled(ChannelSpan)<{selected?: boolean} & AkeneoThemedProps>`
  ${({selected}) =>
    selected &&
    css`
      color: ${getColor('purple100')};
      font-style: italic;
      font-weight: bold;
    `}
`;

const ChannelDropdownItem = styled(Dropdown.Item)`
  justify-content: space-between;
  align-items: center;
  white-space: nowrap;
`;

type ChannelProps = {
  code: string;
  label: string;
};

type ChannelSelectorProps = {
  value: ChannelCode;
  values: Channel[];
  completeValues?: ChannelCode[]
  onChange: (value: ChannelCode) => void;
}
const ChannelSelector = ({value, values, completeValues, onChange}: ChannelSelectorProps) => {
  const translate = useTranslate();
  const [isOpen, open, close] = useBooleanState(false);
  const selectedChannel: Channel = values.find(channel => channel.code === value) || values[0];

  const handleChange = (channelCode: ChannelCode) => onChange?.(channelCode);

  return (
    <DropdownContainer>
      <SwitcherButton inline onClick={() => {open();}} label={translate('pim_common.channel')}>
        <HighlightChannel code={"["+selectedChannel.code+"]"} label={selectedChannel.labels['en_US']} />
      </SwitcherButton>
      {isOpen && (
        <Dropdown.Overlay verticalPosition="down" onClose={close}>
          <Dropdown.Header>
            <Dropdown.Title>{translate('pim_common.channel')}</Dropdown.Title>
          </Dropdown.Header>
          <Dropdown.ItemCollection>
            {values.map(channel => {
                return (
                  <ChannelDropdownItem
                    aria-selected={channel.code === value}
                    key={channel.code}
                    onClick={() => {
                      close();
                      handleChange(channel.code);
                    }}
                  >
                    <HighlightChannel
                      code={"["+channel.code+"]"}
                      label={channel.labels['en_US']}
                      selected={channel.code === value}
                      key={channel.code}
                    />
                    {completeValues && !completeValues.includes(channel.code) && (
                        <Pill level="warning" data-testid={`ChannelSelector.incomplete.${channel.code}`} />
                    )}
                  </ChannelDropdownItem>
                );
              }
            )}
          </Dropdown.ItemCollection>
        </Dropdown.Overlay>
      )}
    </DropdownContainer>
  );
}

export {ChannelSelector};
