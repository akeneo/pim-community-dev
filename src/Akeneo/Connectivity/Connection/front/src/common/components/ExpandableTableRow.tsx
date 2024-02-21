import React, {Children, createContext, FC, ReactNode, useState} from 'react';
import {getColor, Table} from 'akeneo-design-system';
import styled from 'styled-components';

const LargeCell = styled.td.attrs(({colSpan}) => ({colSpan: colSpan}))`
    border-bottom: 1px solid ${getColor('grey', 60)};
`;
const ShowContextContainer = styled.div`
    display: block;
    margin: 0 auto 20px;
    padding-left: 10px;
    width: 756px;
    overflow-x: scroll;
    border: 1px solid ${getColor('grey', 80)};
    background-color: ${getColor('white')};
`;
const ExpandableRow = styled(Table.Row)<{isExpanded: boolean}>`
    ${({isExpanded}) =>
        isExpanded
            ? `
    > td {
        border-bottom: none;
    }
    `
            : ''};
`;

type Props = {
    contentToExpand: ReactNode;
};

export const IsExpanded = createContext<boolean>(false);

const ExpandableTableRow: FC<Props> = ({contentToExpand, children}) => {
    const [display, setDisplay] = useState(false);
    const handleClick = () => {
        setDisplay(!display);
    };

    return (
        <>
            <ExpandableRow onClick={handleClick} isExpanded={display}>
                <IsExpanded.Provider value={display}>{children}</IsExpanded.Provider>
            </ExpandableRow>
            {display && (
                <Table.Row>
                    <LargeCell colSpan={Children.count(children)} data-testid='expanded-row-large-cell'>
                        <ShowContextContainer>{contentToExpand}</ShowContextContainer>
                    </LargeCell>
                </Table.Row>
            )}
        </>
    );
};

export default ExpandableTableRow;
