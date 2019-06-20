import __ from 'akeneoassetmanager/tools/translator';

const messenger = require('oro/messenger');

export default () => () => (next: any) => (action: any) => {
  if ('NOTIFY' === action.type) {
    messenger.notify(action.level, __(action.message, action.parameters));

    return;
  }

  return next(action);
};
