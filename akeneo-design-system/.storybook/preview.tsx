import React from 'react';
import {addDecorator, addParameters} from '@storybook/react';
import {StoryStyle} from '../src/shared/global';

addDecorator(story => (
  <>
    <StoryStyle>{story()}</StoryStyle>
  </>
));

addParameters({
    viewMode: 'docs'
})
