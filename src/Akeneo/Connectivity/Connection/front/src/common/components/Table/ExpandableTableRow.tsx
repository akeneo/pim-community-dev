import React, {Children, FC, ReactNode, useState} from 'react';
import {getColor, Table} from 'akeneo-design-system';
import styled from 'styled-components';

const LargeCell = styled.td.attrs(({colSpan}) => ({colSpan: colSpan}))`
    border-bottom: 1px solid ${getColor('grey', 60)};
`;
const ShowContextContainer = styled.div`
    display: block;
    margin: 0 auto 20px auto;
    width: 70%;
    border: 1px solid ${getColor('grey', 80)};
    background-color: ${getColor('white')};
`;
const ExpandableRow = styled(Table.Row)<{isExpanded: boolean}>`
    ${({isExpanded}) => (isExpanded ? `
    > td {
        border-bottom: none;
    }
    ` : '')};
`;

type Props = {
    contentToExpand: ReactNode;
};

const ExpandableTableRow: FC<Props> = ({contentToExpand, children}) => {
    const [display, setDisplay] = useState(false);
    const handleClick = () => {
        setDisplay(!display);
    };

    return <>
        <ExpandableRow onClick={handleClick} isExpanded={display}>
            {children}
        </ExpandableRow>
        {display &&
            <Table.Row>
                <LargeCell colSpan={Children.count(children)} data-testid='expanded-row-large-cell'>
                    <ShowContextContainer>
                        {contentToExpand}
                    </ShowContextContainer>
                </LargeCell>
            </Table.Row>
        }
    </>;
};

export default ExpandableTableRow;
