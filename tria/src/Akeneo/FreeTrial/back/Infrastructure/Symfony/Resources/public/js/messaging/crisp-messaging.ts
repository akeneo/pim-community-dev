import {PimMessaging} from './pim-messaging';
import {getCrispAgent} from './crisp-agent';

const UserContext = require('pim/user-context');

const CrispMessaging: PimMessaging = {
  init: () => {
    getCrispAgent().then(crisp => {
      if (crisp === null) {
        return;
      }

      crisp.push(['set', 'user:email', UserContext.get('email')]);
      crisp.push(['set', 'user:nickname', UserContext.get('first_name')]);
    });
  },
};

export = CrispMessaging;
