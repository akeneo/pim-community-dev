import React from 'react';
import {HashRouter as Router, Route, Switch} from 'react-router-dom';
import {ListProductFiles} from './ListProductFiles';
import {ShowProductFile} from './ShowProductFile';

const ProductFileDroppingApp = () => {
    return (
        <Router basename="/retailer-portal">
            <Switch>
                <Route path="/product-file-dropping/:productFileIdentifier">
                    <ShowProductFile />
                </Route>
                <Route path="/product-file-dropping">
                    <ListProductFiles />
                </Route>
            </Switch>
        </Router>
    );
};

export {ProductFileDroppingApp};
