import * as _ from "underscore";
import {EventsHash} from 'backbone';

const $ = require('jquery');
const __ = require('oro/translator');
const BaseForm = require('pim/form');
const UserContext = require('pim/user-context');
const FetcherRegistry = require('pim/fetcher-registry');
const i18n = require('pim/i18n');
const template = require('pim/template/product/scope-switcher');

interface Channel {
  code: string;
  labels: {
    [locale: string]: string;
  }
}

class ChannelSwitcher extends BaseForm
{
  private template = _.template(template);

  private channels: Channel[] = [];

  constructor(options: any) {
    super({...options, ...{className: 'AknDropdown AknButtonList-item scope-switcher'}});
  }

  public events(): EventsHash {
    return {
      'click [data-scope]': 'changeChannel',
    };
  }

  configure () {
    this.listenTo(this.getRoot(), 'pim_enrich:form:locale_switcher:change', (_: any) => {
      this.render();
    });

    return $.when(
      BaseForm.prototype.configure.apply(this, arguments),
      this.fetchChannels()
        .then((channels: Channel[]) => {
          this.channels = channels;
          const currentChannelCode = UserContext.get('catalogScope');
          let currentChannel = this.channels.find((channel: Channel) => channel.code === currentChannelCode);
          if (undefined === currentChannel) {
            [currentChannel] = this.channels;
            UserContext.set('catalogScope', currentChannel.code);
          }
        })
    );
  }

  render() {
    const currentChannelCode = UserContext.get('catalogScope');
    // @ts-ignore
    let currentChannel: Channel = this.channels.find((channel: Channel) => channel.code === currentChannelCode);

    this.$el.html(
      this.template({
        channels: this.channels,
        currentScope: i18n.getLabel(
          currentChannel.labels,
          UserContext.get('catalogLocale'),
          currentChannel.code
        ),
        catalogLocale: UserContext.get('catalogLocale'),
        i18n: i18n,
        displayInline: false,
        displayLabel: true,
        label: __('pim_enrich.entity.channel.uppercase_label')
      })
    );

    return this;
  }

  changeChannel (event: any) {
    UserContext.set('catalogScope', event.currentTarget.dataset.scope);
    this.getRoot().trigger('pim_enrich:form:scope_switcher:change', {
      scopeCode: event.currentTarget.dataset.scope,
      context: "base_product"
    });
    this.render();
  }

  fetchChannels() {
    return FetcherRegistry.getFetcher('channel').fetchAll();
  }
}

export = ChannelSwitcher;
