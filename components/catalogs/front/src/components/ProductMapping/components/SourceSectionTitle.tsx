import {SectionTitle, Tag} from 'akeneo-design-system';
import React, {FC} from 'react';

type Props = {
    order: number;
};

export const SourceSectionTitle: FC<Props> = ({order, children}) => {
    return (
        <SectionTitle>
            <Tag tint='purple'>{order}</Tag>
            <SectionTitle.Title level='secondary'>{children}</SectionTitle.Title>
        </SectionTitle>
    );
};
