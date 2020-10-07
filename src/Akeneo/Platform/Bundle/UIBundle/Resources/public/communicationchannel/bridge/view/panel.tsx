import * as React from 'react';
// @ts-ignore
import {ReactView} from '@akeneo-pim-community/legacy-bridge';
// @ts-ignore
import {Index} from '@akeneo-pim-community/communication-channel';

const mediator = require('oro/mediator');

class PanelView extends ReactView {
  constructor() {
    super({className: 'AknPanel'});
  }

  configure() {
    // @ts-ignore
    this.listenTo(mediator, 'communication-channel:panel:open', this.openPanel);
    // @ts-ignore
    this.listenTo(mediator, 'communication-channel:panel:close', this.closePanel);
    // @ts-ignore
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
    // @ts-ignore
    this.$el.removeClass('AknPanel--collapsed');
    // @ts-ignore
    this.$el.removeClass('AknPanel--no-overflow');
    mediator.trigger('pim-app:overlay:show');
  }

  closePanelFromOverlay() {
    mediator.trigger('communication-channel:panel:close');
  }

  closePanel() {
    if (!this.isColapsed()) {
      // @ts-ignore
      this.$el.addClass('AknPanel--collapsed');
      // Trick to keep the transition for collapsing the panel on the right (during 0.3s) and fix the bug with the overflow (cf: https://akeneo.atlassian.net/browse/DAPI-1085)
      setTimeout(() => {
        // @ts-ignore
        this.$el.addClass('AknPanel--no-overflow');
      }, 300);
      mediator.trigger('pim-app:overlay:hide');
    }
  }

  isColapsed() {
    // @ts-ignore
    return this.$el.hasClass('AknPanel--collapsed') && this.$el.addClass('AknPanel--no-overflow');
  }
}

export = PanelView;
