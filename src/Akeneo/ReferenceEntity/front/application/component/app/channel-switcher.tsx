import * as React from 'react';
import Channel from 'akeneoreferenceentity/domain/model/channel';
import __ from 'akeneoreferenceentity/tools/translator';
import Dropdown, {DropdownElement} from 'akeneoreferenceentity/application/component/app/dropdown';
import Key from 'akeneoreferenceentity/tools/key';

const ChannelItemView = ({
  isOpen,
  element,
  isActive,
  onClick,
}: {
  isOpen: boolean;
  element: DropdownElement;
  isActive: boolean;
  onClick: (element: DropdownElement) => void;
}): JSX.Element => {
  const menuLinkClass = `AknDropdown-menuLink ${isActive ? `AknDropdown-menuLink--active` : ''}`;

  return (
    <div
      className={menuLinkClass}
      data-identifier={element.identifier}
      onClick={() => onClick(element)}
      tabIndex={isOpen ? 0 : -1}
      onKeyPress={event => {
        if (Key.Space === event.key) onClick(element);
      }}
    >
      <span className="label">{element.label}</span>
    </div>
  );
};

const ChannelButtonView = ({selectedElement, onClick}: {selectedElement: DropdownElement; onClick: () => void}) => (
  <div
    className="AknActionButton AknActionButton--light AknActionButton--withoutBorder"
    data-identifier={selectedElement.identifier}
    onClick={onClick}
    tabIndex={0}
    onKeyPress={event => {
      if (Key.Space === event.key) onClick();
    }}
  >
    {__('Channel')}
    :&nbsp;
    <span className="AknActionButton-highlight" data-identifier={selectedElement.identifier}>
      {selectedElement.label}
    </span>
    <span className="AknActionButton-caret" />
  </div>
);

const ChannelSwitcher = ({
  channelCode,
  channels,
  locale,
  className = '',
  onChannelChange,
}: {
  channelCode: string;
  channels: Channel[];
  locale: string;
  className?: string;
  onChannelChange: (channel: Channel) => void;
}) => {
  return (
    <Dropdown
      elements={channels.map((channel: Channel) => {
        return {
          identifier: channel.code,
          label: channel.getLabel(locale),
          original: channel,
        };
      })}
      label={__('Channel')}
      selectedElement={channelCode}
      ItemView={ChannelItemView}
      ButtonView={ChannelButtonView}
      onSelectionChange={(channel: DropdownElement) => onChannelChange(channel.original)}
      className={`channel-switcher ${className}`}
    />
  );
};

export default ChannelSwitcher;
