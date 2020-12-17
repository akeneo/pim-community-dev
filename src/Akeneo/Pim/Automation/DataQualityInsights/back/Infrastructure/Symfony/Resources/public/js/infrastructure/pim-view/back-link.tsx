const {BaseForm: BaseView} = require('pimui/js/view/base');
import ReactDOM from 'react-dom';
import React from 'react';
import {BACK_LINK_SESSION_STORAGE_KEY, BackLinkButton} from '@akeneo-pim-community/data-quality-insights';
import {pimTheme} from 'akeneo-design-system';
import {ThemeProvider} from 'styled-components';

const mediator = require('oro/mediator');

class BackLink extends BaseView {
  configure(): JQueryPromise<any> {
    const backLink: any = sessionStorage.getItem(BACK_LINK_SESSION_STORAGE_KEY);
    if (backLink) {
      mediator.on('route_start', (route: string) => {
        const backLinkParams = JSON.parse(backLink);
        if (backLinkParams.hasOwnProperty('displayLinkRoutes') && !backLinkParams.displayLinkRoutes.includes(route)) {
          sessionStorage.removeItem(BACK_LINK_SESSION_STORAGE_KEY);
        }
      });
    }

    return BaseView.prototype.configure.apply(this, arguments);
  }

  render(): typeof BaseView {
    const backLink: any = sessionStorage.getItem(BACK_LINK_SESSION_STORAGE_KEY);
    if (!backLink) {
      return this;
    }

    const backLinkParams = JSON.parse(backLink);

    ReactDOM.render(
      <ThemeProvider theme={pimTheme}>
        <BackLinkButton
          label={backLinkParams.label}
          route={backLinkParams.route}
          routeParams={backLinkParams.routeParams}
        />
      </ThemeProvider>,
      this.el
    );
    return this;
  }

  remove() {
    ReactDOM.unmountComponentAtNode(this.el);
    return super.remove();
  }
}

export default BackLink;
