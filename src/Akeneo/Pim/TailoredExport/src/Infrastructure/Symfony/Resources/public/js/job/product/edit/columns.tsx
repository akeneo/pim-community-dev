import React from 'react';
import BaseView = require('pimui/js/view/base');
import ReactDOM from 'react-dom';
import {ThemeProvider} from 'styled-components';
import {ColumnsTab} from '@akeneo-pim-enterprise/tailored-export';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {pimTheme} from 'akeneo-design-system';

const __ = require('oro/translator');

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
        <DependenciesProvider>
          <ColumnsTab columnsConfiguration={[]} />
        </DependenciesProvider>
      </ThemeProvider>,
        this.el
    );

    this.el.style = 'height: calc(100vh - 256px)'
    return this;
  }
}

export = ColumnView;
