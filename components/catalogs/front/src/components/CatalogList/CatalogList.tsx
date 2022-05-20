import React, {FC, PropsWithChildren} from 'react';
import {Badge} from 'akeneo-design-system';

type Props = {};

const CatalogList: FC<PropsWithChildren<Props>> = () => {
    return (
        <>
            <Badge level='primary'>CatalogList</Badge>
        </>
    );
};

export {CatalogList};
