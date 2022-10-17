import React from 'react';
import {HashRouter as Router, Route, Switch} from 'react-router-dom';
import {ListProductFiles} from './product-file-dropping/ListProductFiles';
import {ShowProductFile} from './product-file-dropping/ShowProductFile';
import {SupplierIndex} from './supplier-management/SupplierIndex';
import {SupplierEdit} from './supplier-management/SupplierEdit';

const RetailerApp = () => {
    return (
        <Router basename="/retailer-portal">
            <Switch>
                <Route path="/supplier/:supplierIdentifier">
                    <SupplierEdit />
                </Route>
                <Route path="/supplier">
                    <SupplierIndex />
                </Route>
                <Route path="/product-file/:productFileIdentifier">
                    <ShowProductFile />
                </Route>
                <Route path="/product-file">
                    <ListProductFiles />
                </Route>
            </Switch>
        </Router>
    );
};

export {RetailerApp};
