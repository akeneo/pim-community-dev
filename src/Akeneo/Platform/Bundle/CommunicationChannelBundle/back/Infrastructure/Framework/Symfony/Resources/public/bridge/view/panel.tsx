import React from 'react';
import {ReactView} from '@akeneo-pim-community/legacy-bridge/src/bridge/react';
import {Index} from '@akeneo-pim-community/communication-channel';

const mediator = require('oro/mediator');

class PanelView extends ReactView {
  constructor() {
    //@ts-ignore
    super({className: 'AknPanel'});
  }

  configure() {
    this.listenTo(mediator, 'communication-channel:panel:open', this.openPanel);
    this.listenTo(mediator, 'communication-channel:panel:close', this.closePanel);
    this.listenTo(mediator, 'pim-app:panel:close', this.closePanelFromOverlay);

    return super.configure();
  }

  reactElementToMount(): JSX.Element {
    return <Index />;
  }

  render() {
    this.closePanel();

    return super.render();
  }

  openPanel() {
    this.$el.removeClass('AknPanel--collapsed');
    this.$el.removeClass('AknPanel--no-overflow');
    mediator.trigger('pim-app:overlay:show');
  }

  closePanelFromOverlay() {
    mediator.trigger('communication-channel:panel:close');
  }

  closePanel() {
    if (!this.isColapsed()) {
      this.$el.addClass('AknPanel--collapsed');
      // Trick to keep the transition for collapsing the panel on the right (during 0.3s) and fix the bug with the overflow (cf: https://akeneo.atlassian.net/browse/DAPI-1085)
      setTimeout(() => {
        this.$el.addClass('AknPanel--no-overflow');
      }, 300);
      mediator.trigger('pim-app:overlay:hide');
    }
  }

  isColapsed() {
    return this.$el.hasClass('AknPanel--collapsed') && this.$el.addClass('AknPanel--no-overflow');
  }
}

export default PanelView;
