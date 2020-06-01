import React from 'react';
import ReactView from 'akeneocommunicationchannel/bridge/react/react-view';
import {Panel} from 'akeneocommunicationchannel/components/panel';

const mediator = require('oro/mediator');

class PanelView extends ReactView {
  constructor() {
    super({className: 'AknCommunicationChannelPanel'});
  }

  configure() {
    this.listenTo(mediator, 'communication-channel:panel:open', this.openPanel);
    this.listenTo(mediator, 'communication-channel:panel:close', this.closePanel);
    this.listenTo(mediator, 'all', this.closePanelWhenEventTriggered);

    return super.configure();
  }

  reactElementToMount(): JSX.Element {
    return <Panel />;
  }
  
  render() {
    return super.render();
  }

  openPanel() {
    this.$el.removeClass('AknCommunicationChannelPanel--collapsed');
    mediator.trigger('pim-app:overlay:show');
  }

  closePanel() {
    if (!this.isColapsed()) {
      this.$el.addClass('AknCommunicationChannelPanel--collapsed');
      mediator.trigger('pim-app:overlay:hide');
    }
  }

  closePanelWhenEventTriggered(event: string) {
    if (!this.isEventToOpenPanel(event)) {
      this.closePanel();
    }
  }

  isColapsed() {
    return this.$el.hasClass('AknCommunicationChannelPanel--collapsed');
  }

  isEventToOpenPanel(event: string) {
    return 'communication-channel:panel:open' === event || 'pim-app:overlay:show' === event;
  }
}

export = PanelView;
