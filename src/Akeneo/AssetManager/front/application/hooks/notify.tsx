import * as React from 'react';
import __ from 'akeneoassetmanager/tools/translator';
const messenger = require('oro/messenger');

export const useNotify = () =>
  React.useCallback(
    (level: string, labelKey: string, parameters?: object) => messenger.notify(level, __(labelKey, parameters)),
    []
  );
