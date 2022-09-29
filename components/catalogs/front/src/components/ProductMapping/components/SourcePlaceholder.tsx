import React, {FC} from 'react';
import styled from 'styled-components';
import {SectionTitle, AttributesIllustration, Link, getColor, getFontSize} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/shared';

const Placeholder = styled.div`
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    padding: 30px 0;
    gap: 5px;
`;
const Title = styled.div`
    font-size: ${getFontSize('big')};
    text-align: center;
    color: ${getColor('grey', 140)};
    font-weight: 400;
    line-height: 18px;
`;
const SubTitle = styled.div`
    font-size: ${getFontSize('default')};
    color: ${getColor('grey', 120)};
    font-weight: 400;
    line-height: 16px;
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
            <Placeholder>
                <AttributesIllustration size={128} />
                <Title>{translate('akeneo_catalogs.product_mapping.source.placeholder.illustration_title')}</Title>
                <SubTitle>{translate('akeneo_catalogs.product_mapping.source.placeholder.subtitle')}</SubTitle>
                <Link href={'https://help.akeneo.com/'} target='_blank'>
                    {translate('akeneo_catalogs.product_mapping.source.placeholder.link')}
                </Link>
            </Placeholder>
        </>
    );
};
