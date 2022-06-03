import React, {FC, PropsWithChildren} from 'react';
import {Edit} from './components/Edit';
import {ErrorBoundary} from '../ErrorBoundary';

type Props = {
    id: string;
};

const CatalogEdit: FC<PropsWithChildren<Props>> = ({id}) => {
    return (
        <ErrorBoundary>
            <Edit id={id} />
        </ErrorBoundary>
    );
};

export {CatalogEdit};
