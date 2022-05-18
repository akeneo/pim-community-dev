import React, {FC, PropsWithChildren} from 'react';
import {Dummy} from './Dummy';

type Props = {};

const CatalogList: FC<PropsWithChildren<Props>> = () => {
    return (
        <>
            <div>CatalogList</div>
            <Dummy label="foo"/>
        </>
    );
};

export {CatalogList};
