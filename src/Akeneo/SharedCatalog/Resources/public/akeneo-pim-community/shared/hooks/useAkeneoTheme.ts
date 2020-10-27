import {useContext} from 'react';
import {ThemeContext} from 'styled-components';
import {AkeneoTheme} from '../theme';

const useAkeneoTheme = () => useContext<AkeneoTheme>(ThemeContext);

export {useAkeneoTheme};
