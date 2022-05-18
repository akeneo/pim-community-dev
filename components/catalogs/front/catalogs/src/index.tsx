import React from 'react';
import ReactDOM from 'react-dom';
import reportWebVitals from './reportWebVitals';
import {CatalogList} from './component/CatalogList';
import {CatalogEdit} from './component/CatalogEdit';
import {HashRouter as Router, Route, Switch} from 'react-router-dom';

ReactDOM.render(
    <React.StrictMode>
        <Router>
            <Switch>
                <Route path='/:id'>
                    <CatalogEdit />
                </Route>
                <Route path='/'>
                    <CatalogList />
                </Route>
            </Switch>
        </Router>
    </React.StrictMode>,
    document.getElementById('root')
);

// If you want to start measuring performance in your app, pass a function
// to log results (for example: reportWebVitals(console.log))
// or send to an analytics endpoint. Learn more: https://bit.ly/CRA-vitals
reportWebVitals();
