import React from 'react';
import {HashRouter as Router, Route, Switch} from 'react-router-dom';
import {SupplierIndex} from './SupplierIndex';
import {SupplierEdit} from './SupplierEdit';

const SupplierApp = () => {
    return (
        <Router basename="/retailer-portal">
            <Switch>
                <Route path="/supplier/:supplierIdentifier">
                    <SupplierEdit />
                </Route>
                <Route path="/supplier">
                    <SupplierIndex />
                </Route>
            </Switch>
        </Router>
    );
};

export {SupplierApp};
