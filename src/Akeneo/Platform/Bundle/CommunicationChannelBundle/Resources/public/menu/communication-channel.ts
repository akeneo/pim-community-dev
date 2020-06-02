import * as Backbone from 'backbone';
import * as _ from 'underscore';

const __ = require('oro/translator');
const CommunicationChannelTemplate = require('akeneo/template/menu/communication-channel');
const mediator = require('oro/mediator');

class CommunicationChannel extends Backbone.View<any> {
  events() {
    return {
      click: this.onClickButton,
    };
  }

  render(): Backbone.View {
    const template = _.template(CommunicationChannelTemplate);
    this.$el.empty().append(
      template({
        title: __('akeneo_communication_channel.link.title'),
      })
    );

    return Backbone.View.prototype.render.apply(this, arguments);
  }

  onClickButton() {
    mediator.trigger('communication-channel:panel:open');
  }
}

export = CommunicationChannel;
