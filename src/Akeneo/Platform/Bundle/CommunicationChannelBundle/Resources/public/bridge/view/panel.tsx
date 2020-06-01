import React from 'react';
import {ReactView} from '@akeneo-pim-community/legacy-bridge';
import {Panel} from 'akeneocommunicationchannel/components/panel';

const mediator = require('oro/mediator');

class PanelView extends ReactView {
  constructor() {
    super({className: 'AknPanel'});
  }

  configure() {
    this.listenTo(mediator, 'communication-channel:panel:open', this.openPanel);
    this.listenTo(mediator, 'communication-channel:panel:close', this.closePanel);

    return super.configure();
  }

  reactElementToMount(): JSX.Element {
    return <Panel />;
  }
  
  render() {
    this.closePanel();

    return super.render();
  }

  openPanel() {
    this.$el.removeClass('AknPanel--collapsed');
    mediator.trigger('pim-app:overlay:show');
  }

  closePanel() {
    if (!this.isColapsed()) {
      this.$el.addClass('AknPanel--collapsed');
      mediator.trigger('pim-app:overlay:hide');
    }
  }

  isColapsed() {
    return this.$el.hasClass('AknPanel--collapsed');
  }
}

export = PanelView;
