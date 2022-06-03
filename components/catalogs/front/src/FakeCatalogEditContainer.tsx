import React, {FC, PropsWithChildren} from 'react';
import {useParams} from 'react-router';
import {CatalogEdit} from './components/CatalogEdit';

type Props = {};

const FakeCatalogEditContainer: FC<PropsWithChildren<Props>> = () => {
    const {id} = useParams<{id: string}>();

    return <CatalogEdit id={id} />;
};

export {FakeCatalogEditContainer};
