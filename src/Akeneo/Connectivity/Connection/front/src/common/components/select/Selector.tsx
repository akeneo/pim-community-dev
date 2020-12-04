import React, {FC} from 'react';
import styled from '../../styled-with-theme';
import {ArrowDownIcon, getColor} from 'akeneo-design-system';

type Props = {
    children: string;
    onClick: () => void;
};

export const Selector: FC<Props> = ({children, onClick}: Props) => (
    <Container tabIndex={0} onClick={onClick}>
        <Value>{children}</Value>
        <DropdownArrow>
            <ArrowDownIcon />
        </DropdownArrow>
    </Container>
);

const Container = styled.div`
    color: ${({theme}) => theme.color.purple100};
    cursor: pointer;
    display: flex;
    font-size: ${({theme}) => theme.fontSize.default};
    height: 44px;
    line-height: 44px;
    outline: none;
`;

const Value = styled.div`
    overflow: hidden;
    white-space: nowrap;
    text-overflow: ellipsis;
    max-width: 240px;
`;

const DropdownArrow = styled.div`
    align-items: center;
    display: flex;
    height: 44px;
    padding-left: 10px;
    padding-right: 2px;
    color: ${getColor('grey', 120)};
    svg {
        width: 16px;
    }
`;
