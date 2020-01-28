import __ from 'akeneoassetmanager/tools/translator';

const messenger = require('oro/messenger');

export default (level: string, message: string, parameters?: object) =>
  messenger.notify(level, __(message, parameters));
