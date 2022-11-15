import React, {FC} from 'react';
import {Badge, SectionTitle} from 'akeneo-design-system';
import {SourcePlaceholder} from './SourcePlaceholder';
import {SelectSourceDropdown} from './SelectSourceDropdown';
import {useTranslate} from '@akeneo-pim-community/shared';
import styled from 'styled-components';

type Props = {
    selectedTarget: string|undefined;
};

export const SourcePanel: FC<Props> = ({selectedTarget}) => {
    const translate = useTranslate();

    return (
        <>
            {selectedTarget === undefined && <SourcePlaceholder/>}
            {selectedTarget && (
                <>
                    <SectionTitle>
                        <SectionTitle.Title>{selectedTarget}</SectionTitle.Title>
                    </SectionTitle>
                    <SectionTitle>
                        <Badge level="secondary">1</Badge>
                        <SectionTitle.Title level="secondary">{translate('akeneo_catalogs.product_mapping.source.title')}</SectionTitle.Title>
                    </SectionTitle>
                    <SelectSourceDropdown></SelectSourceDropdown>
                </>
            )}
        </>
    );
};
