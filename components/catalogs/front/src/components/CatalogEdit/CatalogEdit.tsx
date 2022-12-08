import React, {FC, PropsWithChildren} from 'react';
import {Edit} from './components/Edit';
import {ErrorBoundary} from '../ErrorBoundary';
import {CatalogFormContext} from './contexts/CatalogFormContext';
import {CatalogForm} from './hooks/useCatalogForm';
import {CatalogStatusWidget} from './components/CatalogStatusWidget';

type Props = {
    id: string;
    form: CatalogForm;
    headerContextContainer: HTMLDivElement | undefined;
};

const CatalogEdit: FC<PropsWithChildren<Props>> = ({id, form, headerContextContainer}) => {
    return (
        <ErrorBoundary>
            <CatalogFormContext.Provider value={form.dispatch}>
                <CatalogStatusWidget
                    values={form.values}
                    errors={form.errors}
                    headerContextContainer={headerContextContainer}
                />
                <Edit id={id} values={form.values} errors={form.errors} />
            </CatalogFormContext.Provider>
        </ErrorBoundary>
    );
};

export {CatalogEdit};
