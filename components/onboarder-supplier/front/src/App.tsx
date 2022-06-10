import React from 'react';
import styled, {ThemeProvider} from 'styled-components';
import {CommonStyle, onboarderTheme} from 'akeneo-design-system';
import {QueryClientProvider} from 'react-query';
import {BrowserRouter, Route, Switch} from 'react-router-dom';
import {Authentication} from './Authentication';
import {UserContextProvider} from './contexts';
import {queryClient} from './api';
import {IntlProvider} from 'react-intl';

function App() {
    return (
        <ThemeProvider theme={onboarderTheme}>
            <IntlProvider locale="en" defaultLocale="en" messages={{}}>
                <UserContextProvider>
                    <QueryClientProvider client={queryClient}>
                        <BrowserRouter>
                            <Switch>
                                <Route path={`/(set-up-password|login)/`}>
                                    <Container>
                                        <Authentication />
                                    </Container>
                                </Route>
                            </Switch>
                        </BrowserRouter>
                    </QueryClientProvider>
                </UserContextProvider>
            </IntlProvider>
        </ThemeProvider>
    );
}

const Container = styled.div`
    ${CommonStyle}
`;

export default App;
