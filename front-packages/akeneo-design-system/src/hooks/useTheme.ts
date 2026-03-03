import {useContext} from 'react';
import {ThemeContext} from 'styled-components';
import {Theme} from '../theme';

const useTheme = () => useContext<Theme>(ThemeContext);

export {useTheme};
