import * as React from 'react';
import Channel from 'pimfront/app/domain/model/channel';
import Dropdown, {DropdownElement} from 'pimfront/app/application/component/dropdown';
import * as trans from 'pimenrich/lib/translator';
const userContext = require('pim/user-context');

const ChannelButtonView = (
  {label, selectedElement, onClick}:
  {label: string, selectedElement: DropdownElement, onClick: () => void}
) => (
  <div className="AknActionButton AknActionButton--withoutBorder" onClick={onClick}>
    <div className="AknColumn-subtitle">{trans.get('Channel')}</div>
    <div className="AknColumn-value value">
      {selectedElement.label}
    </div>
  </div>
);

export default ({
  channelCode, channels, onChannelChange}:
  {channelCode: string, channels: Channel[], onChannelChange: (channel: Channel) => void}
) => {
  return <Dropdown
    elements={channels.map((channel: Channel) => {
      return {
        identifier: channel.code,
        label: channel.labels[userContext.get('uiLocale')],
        original: channel
      };
    })}
    label={trans.get('Channel')}
    selectedElement={channelCode}
    ButtonView={ChannelButtonView}
    onSelectionChange={(selection: string) => {
      const channel = channels.find((channel: Channel) => channel.code === selection);

      if (undefined !== channel) {
        onChannelChange(channel);
      }
    }}
  />
};
