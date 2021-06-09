import React from 'react';
import ReactDOM from 'react-dom';
import {DependenciesProvider} from "@akeneo-pim-community/legacy-bridge";
import {InviteUserButton} from '@akeneo-pim-community/invite-user';
import {pimTheme} from "akeneo-design-system";
import {ThemeProvider} from "styled-components";

const BaseView = require('pimui/js/view/base');

class InviteUserButtonView extends BaseView {
  public render() {
    ReactDOM.render(
        <DependenciesProvider>
            <ThemeProvider theme={pimTheme}>
              <InviteUserButton/>
            </ThemeProvider>
        </DependenciesProvider>,
      this.el
    );

    return this;
  }

  public remove() {
    return super.remove();
  }
}

export = InviteUserButtonView;
