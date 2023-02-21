import React, {Children, FC} from 'react';
import {AppIllustration, getColor, getFontSize, Helper, SectionTitle} from 'akeneo-design-system';
import styled from 'styled-components';

const CardGrid = styled.section`
    margin: 20px 0;
    display: grid;
    grid-template-columns: repeat(2, minmax(300px, 1fr));
    gap: 20px;
`;

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
    warningMessage?: string | null | undefined;
    emptyMessage: string;
};

const Section: FC<Props> = ({title, information, emptyMessage, warningMessage, children}) => {
    return (
        <>
            <SectionTitle>
                <SectionTitle.Title>{title}</SectionTitle.Title>
                <SectionTitle.Spacer />
                <SectionTitle.Information>{information}</SectionTitle.Information>
            </SectionTitle>
            {warningMessage && <Helper level={'warning'}>{warningMessage}</Helper>}
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

export {Section, CardGrid};
