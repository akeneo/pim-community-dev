import React, {FC} from 'react';
import {ChevronDownIcon} from '../../icons';
import styled from '../../styled-with-theme';

type Props = {
    children: string;
    onClick: () => void;
};

export const Selector: FC<Props> = ({children, onClick}: Props) => (
    <Container tabIndex={0} onClick={onClick}>
        <Value>{children}</Value>
        <DropdownArrow>
            <ChevronDownIcon />
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
    svg {
        width: 16px;
    }
`;
