import React from 'react';
import {HashRouter as Router, Route, Switch} from 'react-router-dom';
import {ListProductFiles} from './ListProductFiles';
import {ShowProductFile} from './ShowProductFile';

const ProductFileDroppingApp = () => {
    return (
        <Router basename="/retailer-portal/product-file-dropping">
            <Switch>
                <Route path="/:productFileIdentifier">
                    <ShowProductFile />
                </Route>
                <Route path="/">
                    <ListProductFiles />
                </Route>
            </Switch>
        </Router>
    );
};

export {ProductFileDroppingApp};
