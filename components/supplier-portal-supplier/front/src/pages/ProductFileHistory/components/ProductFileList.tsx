import React from 'react';
import {ProductFile} from '../model/ProductFile';
import {ArrowRightIcon, DownloadIcon, getColor, IconButton, Table, SectionTitle} from 'akeneo-design-system';
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
    margin: 10px 50px 0 50px;
    width: auto;
`;

const StyledActionCell = styled(Table.ActionCell)`
    width: 32px;
    padding-right: 6px;
    padding-left: 6px;
`;

const StyledDownloadIcon = styled(DownloadIcon)`
    color: ${getColor('grey100')};
`;

const StyledArrowRightIcon = styled(ArrowRightIcon)`
    color: ${getColor('grey100')};
`;

const StyledIconButton = styled(IconButton)`
    color: ${getColor('grey100')};

    &:hover:not([disabled]) {
        background-color: transparent;
        color: ${getColor('grey100')};
    }
`;

const StyledSectionTitle = styled(SectionTitle)`
    margin: 50px 50px 0px;
    width: auto;
    display: flex;
    justify-content: space-between;
`;

const StyledNumberOfProductFiles = styled.span`
    color: #3c86b3;
    float: right;
    text-transform: lowercase;
`;

const ProductFileList = ({productFiles}: Props) => {
    const dateFormatter = useDateFormatter();
    const intl = useIntl();

    return (
        <>
            <StyledSectionTitle>
                <SectionTitle.Title>
                    <FormattedMessage defaultMessage="File history" id="E+F5l+" />
                </SectionTitle.Title>
                <StyledNumberOfProductFiles>
                    <FormattedMessage
                        defaultMessage="{numberOfProductFiles, plural, one {# result} other {# results}}"
                        id="OEGUss"
                        values={{
                            numberOfProductFiles: productFiles.length,
                        }}
                    />
                </StyledNumberOfProductFiles>
            </StyledSectionTitle>
            <StyledTable>
                <Table.Header>
                    <Table.HeaderCell>
                        <FormattedMessage defaultMessage="Upload date" id="kibDSA" />
                    </Table.HeaderCell>
                    <Table.HeaderCell>
                        <FormattedMessage defaultMessage="Contributor" id="+k5t/y" />
                    </Table.HeaderCell>
                    <Table.HeaderCell>
                        <FormattedMessage defaultMessage="File name" id="ppAn7O" />
                    </Table.HeaderCell>
                    <Table.HeaderCell></Table.HeaderCell>
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
                                <StyledActionCell>
                                    <StyledIconButton
                                        data-testid="Download icon"
                                        icon={<StyledDownloadIcon size={20} animateOnHover={true} />}
                                        title={intl.formatMessage({
                                            defaultMessage: 'Download',
                                            id: '5q3qC0',
                                        })}
                                        ghost={'borderless'}
                                        href={'/supplier-portal/download-file/' + productFile.identifier}
                                    />
                                </StyledActionCell>
                                <StyledActionCell>
                                    <StyledIconButton
                                        data-testid="Arrow icon"
                                        icon={<StyledArrowRightIcon size={20} />}
                                        title={intl.formatMessage({
                                            defaultMessage: 'Arrow',
                                            id: 'UTlLBb',
                                        })}
                                        ghost={'borderless'}
                                        onClick={() => {}}
                                    />
                                </StyledActionCell>
                            </Table.Row>
                        );
                    })}
                </Table.Body>
            </StyledTable>
        </>
    );
};

export {ProductFileList};
