import React from 'react';
import {create} from 'react-test-renderer';
import {ThemeProvider} from 'styled-components';
import {theme} from '../../src/application/common/theme';

export const createWithTheme: typeof create = (nextElement, options?) =>
    create(<ThemeProvider theme={theme}>{nextElement}</ThemeProvider>, options);
