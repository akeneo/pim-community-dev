import BaseView = require('pimui/js/view/base');
import ReactDOM from 'react-dom';
import React from 'react';
import {BackLinkButton, BACK_LINK_SESSION_STORAGE_KEY} from 'akeneodataqualityinsights-react';

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

  render(): BaseView {
    const backLink: any = sessionStorage.getItem(BACK_LINK_SESSION_STORAGE_KEY);
    if (!backLink) {
      return this;
    }

    const backLinkParams = JSON.parse(backLink);

    ReactDOM.render(
      <BackLinkButton
        label={backLinkParams.label}
        route={backLinkParams.route}
        routeParams={backLinkParams.routeParams}
      />,
      this.el
    );
    return this;
  }

  remove() {
    ReactDOM.unmountComponentAtNode(this.el);
    return super.remove();
  }
}

export = BackLink;
