import React, {FC} from 'react';
import {useTranslate} from '@akeneo-pim-community/shared';

type Props = {
    sourceDefaultValue: string | boolean | number | null;
};

export const SourceDefaultValue: FC<Props> = ({sourceDefaultValue}) => {
    const translate = useTranslate();

    let valueToDisplay = String(sourceDefaultValue);
    if (typeof sourceDefaultValue === 'boolean') {
        valueToDisplay = sourceDefaultValue ? translate('pim_common.yes') : translate('pim_common.no');
    }

    return (
        <>
            {translate('akeneo_catalogs.product_mapping.source.default_value')}: &quot;{valueToDisplay}&quot;
        </>
    );
};
