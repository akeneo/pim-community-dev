import React from 'react';
import {addDecorator, addParameters} from '@storybook/react';
import {withThemesProvider} from 'themeprovider-storybook';
import {themes} from '../src/theme';
import {StoryStyle} from '../src/storybook/PreviewGallery';

addDecorator(story => <StoryStyle>{story()}</StoryStyle>);

addDecorator(withThemesProvider(themes));

addParameters({
  viewMode: 'docs',
});
