import BaseView = require('pimui/js/view/base');
import * as _ from 'underscore';
import React from 'react';
import ReactDOM from 'react-dom';
import {ThemeProvider} from 'styled-components';
import Column from '@akeneo-pim-community/tailored-export';
import {pimTheme} from 'akeneo-design-system';

const __ = require('oro/translator');
// const userContext = require('pim/user-context');

class ColumnView extends BaseView {
  public config: any;

  constructor(options: {config: any}) {
    super(options);

    this.config = {...this.config, ...options.config};
  }

  configure() {
    this.trigger('tab:register', {
      code: this.config.tabCode ? this.config.tabCode : this.code,
      label: __(this.config.tabTitle),
    });

    return BaseView.prototype.configure.apply(this, arguments);
  }

  /**
   * {@inheritdoc}
   */
  render(): BaseView {
    ReactDOM.render(
      <ThemeProvider theme={pimTheme}>
        <Column jobCode="test" />
      </ThemeProvider>,
      this.el
    );

    return this;
  }
}

export = ColumnView;
