// const router = require('pim/router');
import * as trans from 'pimui/lib/translator';
const viewBuilder = require('pim/form-builder');
// const messenger = require('oro/messenger');
const userContext = require('pim/user-context');
// const securityContext = require('pim/security-context');

const __ = (key: string, placeholders: any = {}, number: number = 1) => {
  const translation = trans.get(key, {...placeholders}, number);

  return undefined === translation ? key : translation;
};

export const dependencies = {
  // router,
  translate: __,
  legacy: {
    viewBuilder,
  },
  // notify: messenger.notify.bind(messenger),
  user: userContext.get,
  // security: {
  //   isGranted: securityContext.isGranted.bind(securityContext),
  // },
};
