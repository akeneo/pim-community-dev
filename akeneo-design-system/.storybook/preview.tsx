import React from 'react';
import {addDecorator, addParameters} from '@storybook/react';
import {withThemesProvider} from 'themeprovider-storybook';
import {StoryStyle} from '../src/storybook/global';
import {themes} from '../src/theme';

addDecorator(story => (
  <>
    <StoryStyle>{story()}</StoryStyle>
  </>
));

addDecorator(withThemesProvider(themes));

addParameters({
  viewMode: 'docs',
});
