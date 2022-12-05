import React, {FC} from 'react';
import styled from 'styled-components';
import {AttributesIllustration, Link, Placeholder, SectionTitle} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/shared';

const SectionContent = styled.div`
    padding: 30px 0;
    text-align: center;
`;

type Props = {
    targetLabel: string | null;
};

export const SourceUuidPlaceholder: FC<Props> = ({targetLabel}) => {
    const translate = useTranslate();

    return (
        <>
            <SectionTitle>
                <SectionTitle.Title>{targetLabel}</SectionTitle.Title>
            </SectionTitle>
            <SectionContent>
                <Placeholder
                    illustration={<AttributesIllustration />}
                    title={translate('akeneo_catalogs.product_mapping.source.uuid_placeholder.illustration_title')}
                >
                    <p>{translate('akeneo_catalogs.product_mapping.source.uuid_placeholder.subtitle')}</p>
                    <Link
                        href={
                            'https://help.akeneo.com/pim/serenity/articles/manage-product-identifiers.html#description-of-the-uuid-the-new-technical-product-identifier'
                        }
                        target='_blank'
                    >
                        {translate('akeneo_catalogs.product_mapping.source.uuid_placeholder.link')}
                    </Link>
                </Placeholder>
            </SectionContent>
        </>
    );
};
