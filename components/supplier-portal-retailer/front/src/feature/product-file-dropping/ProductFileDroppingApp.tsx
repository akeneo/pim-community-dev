import React from 'react';
import {HashRouter as Router, Route, Switch} from 'react-router-dom';
import {ListSupplierFiles} from './ListSupplierFiles';

const ProductFileDroppingApp = () => {
    return (
        <Router basename="/retailer-portal/product-file-dropping">
            <Switch>
                <Route path="/">
                    <ListSupplierFiles />
                </Route>
            </Switch>
        </Router>
    );
};

export {ProductFileDroppingApp};
