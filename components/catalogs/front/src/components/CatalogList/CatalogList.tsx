import React, {FC, PropsWithChildren} from 'react';
import {List} from './components/List';
import {ErrorBoundary} from '../ErrorBoundary';
import {Badge} from 'akeneo-design-system';
import {useLegacyTranslate} from '@akeneo-pim-community/shared';

type Props = {
    owner: string;
    onCatalogClick: (catalogId: string) => void;
};

const CatalogList: FC<PropsWithChildren<Props>> = ({owner, onCatalogClick}) => {
    const translate = useLegacyTranslate();

    return (
        <ErrorBoundary>
            <Badge level='primary'>{translate('akeneo_connectivity.connection.connect.connected_apps.edit.tabs.settings')}</Badge>
            <List owner={owner} onCatalogClick={onCatalogClick} />
        </ErrorBoundary>
    );
};

export {CatalogList};
