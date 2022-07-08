import React, {FC, PropsWithChildren} from 'react';
import {Edit} from './components/Edit';
import {ErrorBoundary} from '../ErrorBoundary';
import {CatalogFormContext} from './contexts/CatalogFormContext';
import {CatalogForm} from './hooks/useCatalogForm';

type Props = {
    form: CatalogForm;
};

const CatalogEdit: FC<PropsWithChildren<Props>> = ({form}) => {
    return (
        <ErrorBoundary>
            <CatalogFormContext.Provider value={form.dispatch}>
                <Edit values={form.values} errors={form.errors} />
            </CatalogFormContext.Provider>
        </ErrorBoundary>
    );
};

export {CatalogEdit};
