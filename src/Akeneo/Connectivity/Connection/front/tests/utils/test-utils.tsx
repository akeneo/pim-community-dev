import {render} from '@testing-library/react';
import React, {PropsWithChildren, ReactElement} from 'react';
import {ThemeProvider} from 'styled-components';
import {theme} from '../../src/common/theme';

const DefaultProviders = ({children}: PropsWithChildren<{}>) => {
    return <ThemeProvider theme={theme}>{children}</ThemeProvider>;
};

const customRender = (ui: ReactElement) => render(ui, {wrapper: DefaultProviders});

export * from '@testing-library/react';
export {customRender as render};
