import React from 'react';
import {Provider} from 'react-redux';
import attributeOptionsStore from './store/store';
import AttributeOptions from './components/AttributeOptions';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {AttributeContextProvider, LocalesContextProvider} from "./contexts";

interface IndexProps {
  attributeId: number;
}

const Index = ({attributeId}: IndexProps) => {
    return (
        <DependenciesProvider>
            <Provider store={attributeOptionsStore}>
                <AttributeContextProvider attributeId={attributeId}>
                    <LocalesContextProvider>
                        <AttributeOptions />
                    </LocalesContextProvider>
                </AttributeContextProvider>
            </Provider>
        </DependenciesProvider>
    );
};

export default Index;
