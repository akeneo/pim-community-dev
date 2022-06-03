import React from 'react';
import ReactDOM from 'react-dom';
import reportWebVitals from './reportWebVitals';
import {HashRouter as Router, Route, Switch, useHistory} from 'react-router-dom';
import {ThemeProvider} from 'styled-components';
import {CatalogList} from './components/CatalogList';
import {pimTheme} from 'akeneo-design-system';
import {QueryClient, QueryClientProvider} from 'react-query';
import {MicroFrontendDependenciesProvider} from '@akeneo-pim-community/shared';
import {FakePIM} from './FakePIM';
import {FakeCatalogEditContainer} from './FakeCatalogEditContainer';

const routes = {
    pim_user_user_rest_get_current: {
        tokens: [['text', '/rest/user/']],
        defaults: [],
        requirements: [],
        hosttokens: [],
        methods: ['GET'],
        schemes: [],
    },
    pim_user_security_rest_get: {
        tokens: [['text', '/rest/security/']],
        defaults: [],
        requirements: {
            method: 'GET',
        },
        hosttokens: [],
        methods: [],
        schemes: [],
    },
};

const client = new QueryClient();

const handleCatalogClick = (catalogId: string) => {
    // @todo try to use useHistory
    // const history = useHistory();
    // history.push('/' + catalogId);

    window.location.assign('/#/' + catalogId);
};

ReactDOM.render(
    <React.StrictMode>
        <ThemeProvider theme={pimTheme}>
            <QueryClientProvider client={client}>
                <MicroFrontendDependenciesProvider
                    routes={routes}
                    translations={{
                        locale: 'en_US',
                        messages: {},
                    }}
                >
                    <FakePIM>
                        <Router>
                            <Switch>
                                <Route path='/:id'>
                                    <FakeCatalogEditContainer />
                                </Route>
                                <Route path='/'>
                                    <CatalogList
                                        owner='app_cbza17p7cr48gog4c8gg84gw8'
                                        onCatalogClick={handleCatalogClick}
                                    />
                                </Route>
                            </Switch>
                        </Router>
                    </FakePIM>
                </MicroFrontendDependenciesProvider>
            </QueryClientProvider>
        </ThemeProvider>
    </React.StrictMode>,
    document.getElementById('root')
);

// If you want to start measuring performance in your app, pass a function
// to log results (for example: reportWebVitals(console.log))
// or send to an analytics endpoint. Learn more: https://bit.ly/CRA-vitals
reportWebVitals();
