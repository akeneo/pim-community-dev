import React, {FC, PropsWithChildren, ReactElement} from 'react';
import styled from 'styled-components';
import {getColor, getFontSize, SectionTitle} from 'akeneo-design-system';
import translate from '../dependencies/translate';

const H3 = styled.h3`
    color: ${getColor('grey', 140)};
    text-transform: uppercase;
    font-size: 15px;
    font-weight: 400;
`;
const FieldContainer = styled.div`
    width: 100%;
    min-height: 43px;
    padding: 27px 12px;
    border-bottom: 1px solid ${getColor('grey', 60)};
    color: ${getColor('grey', 140)};
    display: flex;
`;
const Level = styled.span`
    color: ${getColor('purple', 100)};
    font-style: italic;
    font-size: ${getFontSize('default')};
    margin-left: 10px;
    display: inline-block;
    vertical-align: super;
    line-height: normal;
`;
const LevelContainer = styled.div`
    width: 70px;
    margin-right: 68px;
`;
const LevelItemsContainer = styled.div`
    width: 390px;
    line-height: 16px;
    margin-top: 3px;
`;
const StyledSection = styled.div`
    margin-bottom: 10px;
`;

type FieldProps = {
    levelLabel: string;
    icon: ReactElement;
};
const LevelSummaryField: FC<PropsWithChildren<FieldProps>> = ({children, levelLabel, icon}) => (
    <FieldContainer>
        <LevelContainer>
            {icon}
            <Level>{translate(levelLabel)}</Level>
        </LevelContainer>
        <LevelItemsContainer>{children}</LevelItemsContainer>
    </FieldContainer>
);

type SectionProps = {
    label: string;
};
const PermissionSectionSummary: FC<PropsWithChildren<SectionProps>> = ({label, children}) => (
    <StyledSection>
        <SectionTitle>
            <H3>{translate(label)}</H3>
        </SectionTitle>
        <div>{children}</div>
    </StyledSection>
);

export {PermissionSectionSummary, LevelSummaryField};
