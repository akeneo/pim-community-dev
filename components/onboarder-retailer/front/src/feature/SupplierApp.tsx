import React from 'react';
import {HashRouter as Router, Route, Switch} from 'react-router-dom';
import {SupplierIndex} from './SupplierIndex';
import {SupplierEdit} from './SupplierEdit';

const SupplierApp = () => {
    return (
        <Router basename="/onboarder-retailer/v2/supplier">
            <Switch>
                <Route path="/:supplierIdentifier">
                    <SupplierEdit />
                </Route>
                <Route path="/">
                    <SupplierIndex />
                </Route>
            </Switch>
        </Router>
    );
};

export {SupplierApp};
