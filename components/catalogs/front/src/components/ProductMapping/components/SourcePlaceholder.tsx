import React from 'react';
import {SectionTitle} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/shared';

export  const SourcePlaceholder = () => {
    const translate = useTranslate();

    return (
        <SectionTitle>
            <SectionTitle.Title>
                {translate('akeneo_catalogs.product_mapping.source.placeholder.title')}
            </SectionTitle.Title>
        </SectionTitle>
    );
};
