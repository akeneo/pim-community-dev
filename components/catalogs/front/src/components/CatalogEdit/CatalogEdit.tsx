import React, {forwardRef, PropsWithRef} from 'react';
import {Edit} from './components/Edit';
import {ErrorBoundary} from '../ErrorBoundary';

type CatalogEditRef = {
    save: () => void;
} | null;
type Props = {
    id: string;
    onChange: (isDirty: boolean) => void;
};

const CatalogEdit = forwardRef<CatalogEditRef, PropsWithRef<Props>>(({id, onChange}, ref) => {
    return (
        <ErrorBoundary>
            <Edit id={id} onChange={onChange} ref={ref} />
        </ErrorBoundary>
    );
});

export {CatalogEdit};
export type {CatalogEditRef};
