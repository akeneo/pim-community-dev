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

  initialize() {
    this.listenTo(mediator, 'communication-channel:announcements:new', this.renderColouredDot);

    return super.initialize();
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

  renderColouredDot() {
    const span = document.createElement('span');
    span.setAttribute('class', 'AknCommunicationChannelMenu-colouredDot');
    this.$el.find('.AknCommunicationChannelMenu-link').append(span);
  }
}

export = CommunicationChannel;
