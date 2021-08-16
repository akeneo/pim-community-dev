import {getAppcuesAgent} from './appcues-agent';
import {PimOnboarding} from './pim-onboarding';

const FeatureFlags = require('pim/feature-flags');
const UserContext = require('pim/user-context');
const Mediator = require('oro/mediator');

const AppcuesOnboarding: PimOnboarding = {
  registerUser: () => {
    getAppcuesAgent().then(appcues => {
      console.log('test', appcues);
      if (!FeatureFlags.isEnabled('free_trial') || appcues === null) {
        return;
      }

      appcues.identify(UserContext.get('username'));
    });
  },
  page: () => {
    getAppcuesAgent().then(appcues => {
      if (!FeatureFlags.isEnabled('free_trial') || appcues === null) {
        return;
      }

      appcues.page();
    });
  },
  track: () => {},
  loadLaunchpad: (element: string) => {
    getAppcuesAgent().then(appcues => {
      if (!FeatureFlags.isEnabled('free_trial') || appcues === null) {
        return;
      }

      appcues.loadLaunchpad(element, {
        //Optionally specify the position of the content relative to the Launchpad icon. Possible values are as followed:
        //	- center (default value, i.e. bottom-center)
        //	- left (i.e. bottom-left)
        //	- right (i.e. bottom-right)
        //	- top (i.e. top-center)
        //	- top-left
        //	- top-right
        position: "left",
        // Optionally add a header or footer. This must be HTML.
        header: "<h1>Tutorials</h1>",
        footer: "<p>Your footer here</p>",
        // And if you'd prefer to use a custom icon rather than the default "bell" icon, you can optionally pass
        // in an icon as well. Make sure that src is set to the right resource, whether it's in your site's directory or hosted
        icon: '/bundles/akeneofreetrial/icons/LaunchpadIcon.svg'
      });
    });
  },
  init: () => {
    Mediator.on('route_complete', async () => {
      AppcuesOnboarding.page();
    });
    AppcuesOnboarding.registerUser();
    AppcuesOnboarding.loadLaunchpad('#appcues-launchpad-btn');
  },
};

export = AppcuesOnboarding;
