import React from 'react';
import {Provider} from 'react-redux';
import attributeOptionsStore from './store/store';
import AttributeOptions from './components/AttributeOptions';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {AttributeContextProvider} from "./contexts";

interface IndexProps {
  attributeId: number;
}

const Index = ({attributeId}: IndexProps) => {
    return (
        <DependenciesProvider>
            <Provider store={attributeOptionsStore}>
                <AttributeContextProvider attributeId={attributeId}>
                    <AttributeOptions />
                </AttributeContextProvider>
            </Provider>
        </DependenciesProvider>
    );
};

export default Index;
