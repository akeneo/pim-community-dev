import React from 'react';
import styled, {ThemeProvider} from 'styled-components';
import {CommonStyle, onboarderTheme} from 'akeneo-design-system';
import {QueryClientProvider} from 'react-query';
import {HashRouter, Route, Switch} from 'react-router-dom';
import {Authentication} from './Authentication';
import {UserContextProvider} from './contexts';
import {queryClient} from './api';
import {IntlProvider} from 'react-intl';
import {ToastProvider} from "./utils/toaster";

function App() {
    return (
        <ThemeProvider theme={onboarderTheme}>
            <IntlProvider locale="en" defaultLocale="en" messages={{}}>
                <ToastProvider>
                    <UserContextProvider>
                        <QueryClientProvider client={queryClient}>
                            <HashRouter>
                                <Switch>
                                    <Route path={`/(set-up-password|login)/`}>
                                        <Container>
                                            <Authentication />
                                        </Container>
                                    </Route>
                                </Switch>
                            </HashRouter>
                        </QueryClientProvider>
                    </UserContextProvider>
                </ToastProvider>
            </IntlProvider>
        </ThemeProvider>
    );
}

const Container = styled.div`
    ${CommonStyle}
`;

export default App;
