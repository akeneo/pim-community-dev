import React, {FC, PropsWithChildren} from 'react';
import {Edit} from './components/Edit';
import {ErrorBoundary} from '../ErrorBoundary';
import {CatalogFormContext} from './contexts/CatalogFormContext';
import {CatalogForm} from './hooks/useCatalogForm';

type Props = {
    id: string;
    form: CatalogForm;
};

const CatalogEdit: FC<PropsWithChildren<Props>> = ({id, form}) => {
    return (
        <ErrorBoundary>
            <CatalogFormContext.Provider value={form.dispatch}>
                <Edit id={id} values={form.values} errors={form.errors} />
            </CatalogFormContext.Provider>
        </ErrorBoundary>
    );
};

export {CatalogEdit};
