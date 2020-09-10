import React from 'react';
import {addDecorator, addParameters} from '@storybook/react';
import {withThemesProvider} from 'themeprovider-storybook';
import {StoryStyle} from '../src/storybook/global';
import {pimTheme} from '../src/theme/pim';

addDecorator(story => (
  <>
    <StoryStyle>{story()}</StoryStyle>
  </>
));

addDecorator(withThemesProvider([pimTheme]));

addParameters({
  viewMode: 'docs',
});
