import React from 'react';
import {ProductFile} from '../model/ProductFile';
import {Table} from 'akeneo-design-system';
import {FormattedMessage} from 'react-intl';
import styled from 'styled-components';

type Props = {
    productFiles: ProductFile[];
};

const FilenameCell = styled.span`
    text-overflow: ellipsis;
    overflow: hidden;
`;

const StyledTable = styled(Table)`
    margin: 60px 40px 0 40px;
    width: auto;
`;

const ProductFileList = ({productFiles}: Props) => {
    return (
        <>
            <StyledTable>
                <Table.Header>
                    <Table.HeaderCell>
                        <FormattedMessage defaultMessage="Upload date" id="kibDSA" />
                    </Table.HeaderCell>
                    <Table.HeaderCell>
                        <FormattedMessage defaultMessage="Contributor" id="+k5t/y" />
                    </Table.HeaderCell>
                    <Table.HeaderCell>
                        <FormattedMessage defaultMessage="Product file name" id="2stUwi" />
                    </Table.HeaderCell>
                </Table.Header>
                <Table.Body>
                    {productFiles.map((productFile: ProductFile) => {
                        return (
                            <Table.Row key={productFile.identifier}>
                                <Table.Cell>{productFile.uploadedAt}</Table.Cell>
                                <Table.Cell>{productFile.contributor}</Table.Cell>
                                <Table.Cell>
                                    <FilenameCell>{productFile.filename}</FilenameCell>
                                </Table.Cell>
                            </Table.Row>
                        );
                    })}
                </Table.Body>
            </StyledTable>
        </>
    );
};

export {ProductFileList};
