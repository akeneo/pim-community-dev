import { createGlobalStyle } from 'styled-components';
import TwemojiCountryFlags from '../static/fonts/twemoji/TwemojiCountryFlags.woff2';

export const GlobalStyle = createGlobalStyle`
    @font-face {
      font-family: 'Windows Flag Emoji';
      unicode-range: U+1F1E6-1F1FF;
      src: url(${TwemojiCountryFlags}) format('woff2');
    }
`;
