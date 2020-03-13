import {__} from 'akeneomeasure/bridge/legacy/translator';

const router = require('pim/router');
const viewBuilder = require('pim/form-builder');
const userContext = require('pim/user-context');

const dependencies = {
  router,
  translate: __,
  legacy: {
    viewBuilder,
  },
  user: userContext.get,
};

export {dependencies};
