import React, {FC} from 'react';
import {CaretDownIcon, CaretUpIcon} from '../../../common/icons';
import styled from '../../../common/styled-with-theme';

type Order = 'asc' | 'desc';

type Props = {
    order: Order;
    onSort: (order: Order) => void;
};

const SortButton: FC<Props> = ({children, order, onSort}) => {
    return (
        <Button onClick={() => onSort(order === 'asc' ? 'desc' : 'asc')}>
            {children}
            {order === 'asc' ? <CaretDownIcon /> : <CaretUpIcon />}
        </Button>
    );
};

const Button = styled.button`
    background-color: transparent;
    border: 0;
    color: ${({theme}) => theme.color.grey140};
    cursor: pointer;
    outline: none;
    padding: 0;
`;

export {SortButton, Order};
