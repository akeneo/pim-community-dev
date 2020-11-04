/* eslint-disable @typescript-eslint/no-var-requires */
const BaseForm = require('pimui/js/view/base');
import {EventsHash} from 'backbone';
const Router = require('pim/router');
const __ = require('oro/translator');

class CreateButton extends BaseForm {
  private config: any;

  constructor(options: {config: any}) {
    super({
      ...options,
      className: 'AknButton AknButtonList-item AknButton--apply',
    });
    this.config = options.config;
  }

  public events(): EventsHash {
    return {
      click: (event: any) => {
        event.preventDefault();
        Router.redirectToRoute(this.config.url);
      },
    };
  }

  public render() {
    this.$el.html(__(this.config.title));

    return this;
  }
}

export = CreateButton;
