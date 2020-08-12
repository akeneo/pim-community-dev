import React from 'react';
import {addDecorator} from '@storybook/react';
import {GlobalStyle} from '../src/shared/global';

addDecorator(story => (
  <>
    <GlobalStyle />
    {story()}
  </>
));

export const parameters = {
  actions: {argTypesRegex: '^on[A-Z].*'},
};
