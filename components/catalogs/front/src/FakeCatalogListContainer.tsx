import React, {FC, PropsWithChildren} from 'react';
import {CatalogList} from './components/CatalogList';
import {useHistory} from 'react-router-dom';

type Props = {};

const FakeCatalogListContainer: FC<PropsWithChildren<Props>> = () => {
    const history = useHistory();

    const handleCatalogClick = (catalogId: string) => {
        history.push('/' + catalogId);
    };

    return <CatalogList owner='shopifi' onCatalogClick={handleCatalogClick} />;
};

export {FakeCatalogListContainer};
