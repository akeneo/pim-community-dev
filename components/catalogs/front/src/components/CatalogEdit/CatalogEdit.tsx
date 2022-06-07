import React, {forwardRef, FC, PropsWithChildren, useImperativeHandle} from 'react';
import {Edit} from './components/Edit';
import {ErrorBoundary} from '../ErrorBoundary';

type Ref = {
    save: () => void;
};
type Props = {
    id: string;
};

const CatalogEdit: FC<PropsWithChildren<Props>> = forwardRef<Ref, Props>(({id}, ref) => {
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
