import {useContext} from 'react';
import {ThemeContext} from 'styled-components';
import {AkeneoTheme} from '../providers';

const useAkeneoTheme = () => useContext<AkeneoTheme>(ThemeContext);

export {useAkeneoTheme};
