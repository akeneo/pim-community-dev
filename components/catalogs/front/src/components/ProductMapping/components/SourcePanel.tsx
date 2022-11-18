import React, {FC, useCallback} from 'react';
import {SectionTitle, Tag} from 'akeneo-design-system';
import {SourcePlaceholder} from './SourcePlaceholder';
import {SelectAttributeDropdown} from './SelectAttributeDropdown';
import {useTranslate} from '@akeneo-pim-community/shared';
import {Source} from '../models/Source';
import {Attribute} from '../../models/Attribute';


type Props = {
    target: string|null;
    source: Source|null;
    onChange: (value : Source) => void;
};

export const SourcePanel: FC<Props> = ({target, source, onChange}) => {
    const translate = useTranslate();
    const handleSourceSelection = useCallback((value: Attribute) => {
        onChange({
            source: value.code,
            locale: null,
            scope: null
        });
    }, [target, onChange]);

    return (
        <>
            {target === null && <SourcePlaceholder/>}
            {target && (
                <>
                    <SectionTitle>
                        <SectionTitle.Title>{target}</SectionTitle.Title>
                    </SectionTitle>
                    <SectionTitle>
                        <Tag tint="purple">1</Tag>
                        <SectionTitle.Title level="secondary">{translate('akeneo_catalogs.product_mapping.source.title')}</SectionTitle.Title>
                    </SectionTitle>
                    <SelectAttributeDropdown
                        code={null !== source && null !== source.source ? source.source : ''}
                        onChange={handleSourceSelection}
                    ></SelectAttributeDropdown>
                </>
            )}
        </>
    );
};
