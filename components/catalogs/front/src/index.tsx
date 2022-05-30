import React from 'react';
import ReactDOM from 'react-dom';
import reportWebVitals from './reportWebVitals';
import {HashRouter as Router, Route, Switch} from 'react-router-dom';
import {ThemeProvider} from 'styled-components';
import {CatalogList} from './components/CatalogList';
import {CatalogEdit} from './components/CatalogEdit';
import {pimTheme} from 'akeneo-design-system';
import {QueryClient, QueryClientProvider} from 'react-query';
import {DependenciesContext} from '@akeneo-pim-community/shared';
import translate from 'pimui/js/translator';

const dependencies = {
    translate,
};

const client = new QueryClient();

ReactDOM.render(
    <React.StrictMode>
        <ThemeProvider theme={pimTheme}>
            <QueryClientProvider client={client}>
                <DependenciesContext.Provider value={dependencies}>
                    <Router>
                        <Switch>
                            <Route path='/:id'>
                                <CatalogEdit />
                            </Route>
                            <Route path='/'>
                                <CatalogList owner='shopifi' />
                            </Route>
                        </Switch>
                    </Router>
                </DependenciesContext.Provider>
            </QueryClientProvider>
        </ThemeProvider>
    </React.StrictMode>,
    document.getElementById('root')
);

// If you want to start measuring performance in your app, pass a function
// to log results (for example: reportWebVitals(console.log))
// or send to an analytics endpoint. Learn more: https://bit.ly/CRA-vitals
reportWebVitals();
