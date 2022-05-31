import React, {FC, PropsWithChildren} from 'react';
import {List} from './components/List';
import {ErrorBoundary} from '../ErrorBoundary';

type Props = {
    owner: string;
};

const CatalogList: FC<PropsWithChildren<Props>> = ({owner}) => {
    return (
        <ErrorBoundary>
            <List owner={owner} />
        </ErrorBoundary>
    );
};

export {CatalogList};
