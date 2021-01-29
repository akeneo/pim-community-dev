import styled, {css, keyframes} from 'styled-components';
import {Color, FontSize, getColor, getFontSize, Palette} from './theme';

const CommonStyle = css`
  font-family: 'Lato';
  color: ${getColor('grey', 120)};
  font-size: ${getFontSize('default')};
  line-height: 20px;
  box-sizing: border-box;
`;

const loadingBreath = keyframes`
  0% {background-position:0 50%}
  50% {background-position:100% 50%}
  100% {background-position:0 50%}
`;

const placeholderStyle = css`
  animation: ${loadingBreath} 2s infinite;
  background: linear-gradient(270deg, #fdfdfd, #eee);
  background-size: 400% 400%;
  border: none;
  color: transparent;
  border-radius: 3px;
  cursor: default;
  outline: none;
  :hover,
  :last-child,
  ::placeholder {
    color: transparent;
  }
  > * {
    opacity: 0;
  }
`;

const color: Color = {
  blue10: '#f5f9fc',
  blue20: '#dee9f4',
  blue40: '#bdd3e9',
  blue60: '#9bbddd',
  blue80: '#7aa7d2',
  blue100: '#5992c7',
  blue120: '#47749f',
  blue140: '#355777',
  green10: '#f0f7f1',
  green20: '#e1f0e3',
  green10: '#f0f7f1',
  green40: '#c2e1c7',
  green60: '#a3d1ab',
  green80: '#85c28f',
  green100: '#67b373',
  green120: '#528f5c',
  green140: '#3d6b45',
  grey20: '#f6f7fb',
  grey40: '#f0f1f3',
  grey60: '#e8ebee',
  grey80: '#c7cbd4',
  grey100: '#a1a9b7',
  grey120: '#67768a',
  grey140: '#11324d',
  purple20: '#eadcf1',
  purple40: '#d4bae3',
  purple60: '#be97d5',
  purple80: '#a974c7',
  purple100: '#9452ba',
  purple120: '#764194',
  purple140: '#58316f',
  red10: '#faefed',
  red20: '#f6dfdc',
  red40: '#eebfb9',
  red60: '#e59f95',
  red80: '#dc7f72',
  red100: '#d4604f',
  red120: '#a94c3f',
  red140: '#7f392f',
  yellow10: '#fef7ec',
  yellow20: '#fef0d9',
  yellow40: '#fde1b2',
  yellow60: '#fbd28b',
  yellow80: '#fac365',
  yellow100: '#f9b53f',
  yellow120: '#c79032',
  yellow140: '#956c25',
  brand20: '#eadcf1',
  brand40: '#d4bae3',
  brand60: '#be97d5',
  brand80: '#a974c7',
  brand100: '#9452ba',
  brand120: '#764194',
  brand140: '#58316f',
  white: '#ffffff',
};

const fontSize: FontSize = {
  big: '15px',
  bigger: '17px',
  default: '13px',
  small: '11px',
  title: '28px',
};

const palette: Palette = {
  primary: 'green',
  secondary: 'blue',
  tertiary: 'grey',
  warning: 'yellow',
  danger: 'red',
};

const BrandedPath = styled.path`
  fill: ${getColor('brand', 100)};
`;

export {color, fontSize, palette, CommonStyle, BrandedPath, placeholderStyle};
