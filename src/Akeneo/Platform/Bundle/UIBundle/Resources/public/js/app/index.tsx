import React from 'react';
import ReactDOM from 'react-dom';
import {bootstrapApplication} from './bootstrap';
import {PimApplication} from './PimApplication';

/* eslint-disable @typescript-eslint/no-var-requires */
const router = require('pim/router');

const domReady = (): Promise<void> =>
  new Promise(resolve => {
    if ('loading' !== document.readyState) {
      resolve();
    } else {
      document.addEventListener('DOMContentLoaded', () => resolve(), {once: true});
    }
  });

/**
 * Boots the PIM and renders the React application shell in the given element.
 */
const mountApplication = async (selector: string = '.app'): Promise<void> => {
  const zones = await bootstrapApplication();
  await domReady();

  ReactDOM.render(<PimApplication zones={zones} onReady={() => router.start()} />, document.querySelector(selector));
};

export {mountApplication};
