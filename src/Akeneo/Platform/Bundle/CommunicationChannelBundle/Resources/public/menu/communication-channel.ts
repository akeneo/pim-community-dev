import * as Backbone from 'backbone';
import * as _ from 'underscore';

const CommunicationChannelTemplate = require('akeneo/template/menu/communication-channel');

class CommunicationChannel extends Backbone.View<any> {
  constructor() {
    super({tagName: 'a'});
  }

  public render(): Backbone.View {
    const template: any = _.template(CommunicationChannelTemplate);
    this.$el.html(template());

    this.$el.attr('href', 'https://help.akeneo.com/pim/v4/');
    this.$el.attr('target', '_blank');

    return Backbone.View.prototype.render.apply(this, arguments);
  }
}

export = CommunicationChannel;
