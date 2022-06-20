import React, {forwardRef, PropsWithRef} from 'react';
import {Edit} from './components/Edit';
import {ErrorBoundary} from '../ErrorBoundary';

type CatalogEditRef = {
    save: () => void;
} | null;
type Props = {
    id: string;
};

const CatalogEdit = forwardRef<CatalogEditRef, PropsWithRef<Props>>(({id}, ref) => {
    return (
        <ErrorBoundary>
            <Edit id={id} ref={ref} />
        </ErrorBoundary>
    );
});

export {CatalogEdit};
export type {CatalogEditRef};
