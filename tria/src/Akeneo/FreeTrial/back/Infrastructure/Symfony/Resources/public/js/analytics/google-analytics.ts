const UserContext = require('pim/user-context');
const Mediator = require('oro/mediator');

declare var gtag: any;

const GoogleAnalytics = {
  /**
   * @see documentation for using Custom dimensions and metrics: https://developers.google.com/analytics/devguides/collection/gtagjs/custom-dims-mets
   */
  init: () => {
    if (typeof gtag !== 'function') return;

    Mediator.on('route_complete', async (name: string, _params?: object) => {
      gtag('set', 'dimension1', UserContext.get('email'));

      gtag('event', 'page_view', {
        page_title: name,
        page_location: window.location.href,
        page_path: `/${window.location.hash}`,
      });
    });
  },
};

export = GoogleAnalytics;
