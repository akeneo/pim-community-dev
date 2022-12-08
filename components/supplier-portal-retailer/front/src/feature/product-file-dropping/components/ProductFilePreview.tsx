import React from 'react';
import {getColor, TableInput} from 'akeneo-design-system';
import {useProductFilePreview} from '../hooks/useProductFilePreview';
import styled from 'styled-components';

const ProductFilePreview = ({productFileIdentifier}: {productFileIdentifier: string}) => {
    const {productFilePreview} = useProductFilePreview(productFileIdentifier);

    if (null == productFilePreview) {
        return <></>;
    }

    return (
        <Container>
            <TableInput>
                <TableInput.Header>
                    <RowNumberCell />
                    {[...Array(productFilePreview.headerRow.length)].map((_, index) => (
                        <TableInput.HeaderCell key={index}>{generateExcelColumnLetter(index)}</TableInput.HeaderCell>
                    ))}
                </TableInput.Header>
                <TableInput.Body>
                    <TableInput.Row>
                        <RowNumberCell>1</RowNumberCell>
                        {productFilePreview.headerRow.map((headerCell, index) => (
                            <TableInput.Cell key={index}>
                                <TableInput.CellContent rowTitle={true}>{headerCell}</TableInput.CellContent>
                            </TableInput.Cell>
                        ))}
                    </TableInput.Row>
                    {productFilePreview.productRows.map((row, rowIndex) => (
                        <TableInput.Row key={rowIndex}>
                            <RowNumberCell>{rowIndex + 2}</RowNumberCell>
                            {row.map((cell, cellIndex) => (
                                <TableInput.Cell key={cellIndex}>
                                    <PreCellContent>{cell}</PreCellContent>
                                </TableInput.Cell>
                            ))}
                        </TableInput.Row>
                    ))}
                </TableInput.Body>
            </TableInput>
        </Container>
    );
};

export const generateExcelColumnLetter = (index: number): string => {
    if (25 >= index) {
        return `${String.fromCharCode(index + 65)}`;
    }
    const modulo = index % 26;
    const nextIndex = (index - modulo) / 26;

    return `${generateExcelColumnLetter(nextIndex - 1)}${String.fromCharCode(modulo + 65)}`;
};

const Container = styled.div`
    overflow: auto;
    max-height: calc(100vh - 285px);
    margin: 20px 0;

    > div {
        overflow: initial;
    }
`;

const RowNumberCell = styled(TableInput.Cell)`
    min-width: 40px;
    width: 40px;
    text-align: center;
    font-weight: bold;
    color: ${getColor('grey', 140)};
    background-color: ${getColor('grey', 40)} !important;
`;

const PreCellContent = styled(TableInput.CellContent)`
    white-space: pre;
`;

export {ProductFilePreview};
