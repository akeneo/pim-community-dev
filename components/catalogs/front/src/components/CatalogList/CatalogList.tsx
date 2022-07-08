import React, {FC, PropsWithChildren} from 'react';
import {List} from './components/List';
import {ErrorBoundary} from '../ErrorBoundary';

type Props = {
    owner: string;
    onCatalogClick: (catalogId: string) => void;
};

const CatalogList: FC<PropsWithChildren<Props>> = ({owner, onCatalogClick}) => {
    return (
        <ErrorBoundary>
            <List owner={owner} onCatalogClick={onCatalogClick} />
        </ErrorBoundary>
    );
};

export {CatalogList};
