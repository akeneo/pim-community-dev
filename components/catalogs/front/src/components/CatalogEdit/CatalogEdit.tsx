import React, {forwardRef, PropsWithRef, useImperativeHandle} from 'react';
import {Edit} from './components/Edit';
import {ErrorBoundary} from '../ErrorBoundary';

type CatalogEditRef = {
    save: () => void;
} | null;
type Props = {
    id: string;
};

const CatalogEdit = forwardRef<CatalogEditRef, PropsWithRef<Props>>(({id}, ref) => {
    useImperativeHandle(ref, () => ({
        save() {
            console.log('Catalog ' + id + ' saved.');
        },
    }));

    return (
        <ErrorBoundary>
            <Edit id={id} />
        </ErrorBoundary>
    );
});

export {CatalogEdit};
export type {CatalogEditRef};
