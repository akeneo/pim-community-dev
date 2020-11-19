import React from 'react';
import {addDecorator, addParameters} from '@storybook/react';
import {withThemesProvider} from 'themeprovider-storybook';
import {themes} from '../src/theme';
import {StoryStyle} from '../src/storybook/PreviewGallery';
import { TranslateContext } from "../src/hooks/useTranslate";

const fakeTranslate = (id, _placeholder, _count) => {
  return id;
}

addDecorator(story => <StoryStyle>
  <TranslateContext.Provider value={fakeTranslate}>
    {story()}
  </TranslateContext.Provider>
</StoryStyle>);

addDecorator(withThemesProvider(themes));

addParameters({
  viewMode: 'docs',
});
