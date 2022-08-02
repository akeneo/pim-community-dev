import React from 'react';
import {ProductFile} from '../model/ProductFile';
import {DownloadIcon, getColor, IconButton, Table} from 'akeneo-design-system';
import {FormattedMessage, useIntl} from 'react-intl';
import styled from 'styled-components';
import {useDateFormatter} from '../../../utils/date-formatter/use-date-formatter';

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

const DownloadCell = styled(Table.ActionCell)`
    width: 50px;
`;

const StyledDownloadIcon = styled(DownloadIcon)`
    color: ${getColor('grey100')};
`;

const StyledIconButton = styled(IconButton)`
    color: ${getColor('grey100')};

    &:hover:not([disabled]) {
        background-color: transparent;
        color: ${getColor('grey100')};
    }
`;

const ProductFileList = ({productFiles}: Props) => {
    const dateFormatter = useDateFormatter();
    const intl = useIntl();

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
                    <Table.HeaderCell></Table.HeaderCell>
                </Table.Header>
                <Table.Body>
                    {productFiles.map((productFile: ProductFile) => {
                        return (
                            <Table.Row key={productFile.identifier}>
                                <Table.Cell>
                                    {dateFormatter(productFile.uploadedAt, {
                                        day: '2-digit',
                                        hour: '2-digit',
                                        minute: '2-digit',
                                        month: '2-digit',
                                        year: 'numeric',
                                    })}
                                </Table.Cell>
                                <Table.Cell>{productFile.contributor}</Table.Cell>
                                <Table.Cell>
                                    <FilenameCell>{productFile.filename}</FilenameCell>
                                </Table.Cell>
                                <DownloadCell>
                                    <StyledIconButton
                                        data-testid="Download icon"
                                        icon={<StyledDownloadIcon animateOnHover={true} />}
                                        title={intl.formatMessage({
                                            defaultMessage: 'Download',
                                            id: '5q3qC0',
                                        })}
                                        ghost={'borderless'}
                                        href={'/supplier-portal/download-file/' + productFile.identifier}
                                    />
                                </DownloadCell>
                            </Table.Row>
                        );
                    })}
                </Table.Body>
            </StyledTable>
        </>
    );
};

export {ProductFileList};
