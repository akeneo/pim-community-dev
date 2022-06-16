import React, {FC, PropsWithChildren} from 'react';
import {Badge} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/shared';

type Props = {
    enabled: boolean;
};

const Status: FC<PropsWithChildren<Props>> = ({enabled}) => {
    const translate = useTranslate();

    return (
        <>
            {enabled ? (
                <Badge level='primary'>{translate('akeneo_catalogs.catalog_list.enabled')}</Badge>
            ) : (
                <Badge level='tertiary'>{translate('akeneo_catalogs.catalog_list.disabled')}</Badge>
            )}
        </>
    );
};

export {Status};
