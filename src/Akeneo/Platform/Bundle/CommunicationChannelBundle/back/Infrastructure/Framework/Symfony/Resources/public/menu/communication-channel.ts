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
    this.listenTo(mediator, 'communication-channel:menu:add_coloured_dot', this.addColouredDot);
    this.listenTo(mediator, 'communication-channel:menu:remove_coloured_dot', this.removeColouredDot);

    return super.initialize();
  }

  render(): Backbone.View {
    const template = _.template(CommunicationChannelTemplate);
    this.$el.empty().append(
      template({
        title: __('akeneo_communication_channel.link.title'),
      })
    );

    if (this.hasNewAnnouncements()) {
      this.addColouredDot();
    }

    return Backbone.View.prototype.render.apply(this, arguments);
  }

  hasNewAnnouncements() {
    if (null === sessionStorage.getItem('communication_channel_has_new_announcements')) {
      return false;
    }

    return JSON.parse(sessionStorage.getItem('communication_channel_has_new_announcements') as string) === true;
  }

  onClickButton() {
    mediator.trigger('communication-channel:panel:open');
  }

  addColouredDot() {
    const span = document.createElement('span');
    span.setAttribute('class', 'AknCommunicationChannelMenu-colouredDot');
    this.$el.find('.AknCommunicationChannelMenu-link').append(span);
  }

  removeColouredDot() {
    const colouredDot = this.$el.find('.AknCommunicationChannelMenu-colouredDot');
    if (colouredDot.length > 0) {
      colouredDot.remove();
    }
  }
}

export {CommunicationChannel};
