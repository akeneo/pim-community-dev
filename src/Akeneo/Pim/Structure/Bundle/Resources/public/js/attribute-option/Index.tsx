import React from 'react';
import {Provider} from 'react-redux';
import attributeOptionsStore from './store/store';
import AttributeOptions from './components/AttributeOptions';

const Index = () => {
    return (
        <Provider store={attributeOptionsStore}>
            <AttributeOptions/>
        </Provider>
    );
};

export default Index;
