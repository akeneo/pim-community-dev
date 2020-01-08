import * as React from 'react';
const router = require('pim/router');

export const useRedirect = () =>
  React.useCallback((route: string, params: object) => router.redirectToRoute(route, params), []);
