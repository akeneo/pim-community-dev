import React from 'react';
import {addDecorator} from '@storybook/react';
import {StoryStyle} from '../src/shared/global';

addDecorator(story => (
  <>
    <StoryStyle>{story()}</StoryStyle>
  </>
));
