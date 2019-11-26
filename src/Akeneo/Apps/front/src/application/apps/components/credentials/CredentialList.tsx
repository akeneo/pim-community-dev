import React, {ReactNode} from 'react';
import styled from 'styled-components';
import {PropsWithTheme} from '../../../common/theme';

export const CredentialList = styled.div`
    display: grid;
    grid-template-columns: repeat(3, auto);
`;

export const CredentialListItem = ({
    label,
    children: value,
    actions,
}: {
    label: ReactNode;
    children: ReactNode;
    actions: ReactNode;
}) => (
    <>
        <LabelColumn>{label}</LabelColumn>
        <ValueColumn>{value}</ValueColumn>
        <ActionsColumn>{actions}</ActionsColumn>
    </>
);

const Column = styled.div`
    border-bottom: 1px solid ${({theme}: PropsWithTheme) => theme.color.mediumGrey};
    height: 54px;
    line-height: 54px;
    padding: 0 10px;
`;

const LabelColumn = styled(Column)`
    color: ${({theme}: PropsWithTheme) => theme.color.purple};
    font-weight: bold;
    padding-left: 20px;
`;

const ValueColumn = styled(Column)`
    color: ${({theme}: PropsWithTheme) => theme.color.darkBlue};
    overflow: hidden;
    text-overflow: ellipsis;
`;

const ActionsColumn = styled(Column)`
    align-items: center;
    display: flex;
    justify-content: flex-end;
    padding-right: 20px;

    > * {
        margin: 0 5px;

        :first-child {
            margin-left: 0;
        }
        :last-child {
            margin-right: 0;
        }
    }
`;
