import React, {Children, FC} from 'react';
import {
    AppIllustration,
    getColor,
    getFontSize,
    SectionTitle,
} from 'akeneo-design-system';
import {Grid as CardGrid} from './MarketplaceCard';
import styled from 'styled-components';

const EmptyContainer = styled.section`
    text-align: center;
    padding: 40px;
`;

const EmptyMessage = styled.p`
    color: ${getColor('grey', 140)};
    font-size: ${getFontSize('big')};
`;

type Props = {
    title: string;
    information: string;
    emptyMessage: string;
}

export const Section: FC<Props> = ({title, information, emptyMessage, children}) => {
    return (
        <>
            <SectionTitle>
                <SectionTitle.Title>{title}</SectionTitle.Title>
                <SectionTitle.Spacer />
                <SectionTitle.Information>{information}</SectionTitle.Information>
            </SectionTitle>
            {Children.count(children) === 0 ? (
                <EmptyContainer>
                    <AppIllustration size={128} />
                    <EmptyMessage>{emptyMessage}</EmptyMessage>
                </EmptyContainer>
            ) : (
                <CardGrid>{children}</CardGrid>
            )}
        </>
    );
};
