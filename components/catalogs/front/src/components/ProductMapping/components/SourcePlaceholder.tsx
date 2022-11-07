import React, {FC} from 'react';
import styled from 'styled-components';
import {AttributesIllustration, Link, Placeholder, SectionTitle} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/shared';

const SectionContent = styled.div`
    padding: 30px 0;
    text-align: center;
`;

export const SourcePlaceholder: FC = () => {
    const translate = useTranslate();

    return (
        <>
            <SectionTitle>
                <SectionTitle.Title>
                    {translate('akeneo_catalogs.product_mapping.source.placeholder.title')}
                </SectionTitle.Title>
            </SectionTitle>
            <SectionContent>
                <Placeholder
                    illustration={<AttributesIllustration />}
                    title={translate('akeneo_catalogs.product_mapping.source.placeholder.illustration_title')}
                >
                    <div>{translate('akeneo_catalogs.product_mapping.source.placeholder.subtitle')}</div>
                    <Link href={'https://help.akeneo.com/'} target='_blank'>
                        {translate('akeneo_catalogs.product_mapping.source.placeholder.link')}
                    </Link>
                </Placeholder>
            </SectionContent>
        </>
    );
};
