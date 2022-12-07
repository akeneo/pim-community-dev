import React from 'react';
import {
    DownloadIcon,
    Dropdown,
    getColor,
    IconButton,
    Pagination,
    Pill,
    Search,
    SwitcherButton,
    Table,
    useBooleanState,
} from 'akeneo-design-system';
import {useDateFormatter, useRouter, useTranslate} from '@akeneo-pim-community/shared';
import styled from 'styled-components';
import {EmptyProductFilesList} from './EmptyProductFilesList';
import {ProductFileRow} from '../models/ProductFileRow';
import {useHistory} from 'react-router';
import {ProductFileImportStatus} from './ProductFileImportStatus';
import {ImportStatus} from '../models/ImportStatus';

export const PRODUCT_FILES_PER_PAGE = 25;

type Props = {
    productFiles: ProductFileRow[];
    totalSearchResults: number;
    currentPage: number;
    onChangePage: (pageNumber: number) => void;
    searchValue: string;
    onSearch: (searchValue: string) => void;
    importStatusValue: null | string;
    handleImportStatusChange: (importStatusValue: null | string) => void;
    displaySupplierColumn?: boolean;
};

const ProductFilesList = ({
    productFiles,
    totalSearchResults,
    currentPage,
    onChangePage,
    searchValue,
    onSearch,
    importStatusValue,
    handleImportStatusChange,
    displaySupplierColumn = true,
}: Props) => {
    const translate = useTranslate();
    const history = useHistory();
    const dateFormatter = useDateFormatter();
    const router = useRouter();
    const [isDropdownOpen, open, close] = useBooleanState();

    const goToProductFile = (productFileIdentifier: string) => {
        history.push(`/product-file/${productFileIdentifier}`);
    };

    const onImportStatusChange = (importStatus: null | string) => {
        handleImportStatusChange(importStatus);
        close();
    };

    return (
        <>
            <StyledSearch
                onSearchChange={onSearch}
                searchValue={searchValue}
                placeholder={translate('supplier_portal.product_file_dropping.supplier_files.search.placeholder')}
            >
                {translate(
                    'supplier_portal.product_file_dropping.supplier_files.search.results_number',
                    {count: totalSearchResults},
                    totalSearchResults
                )}
                <StyledPipe />
                <Dropdown>
                    <SwitcherButton
                        label={translate('supplier_portal.product_file_dropping.supplier_files.status.label')}
                        onClick={open}
                    >
                        {translate(
                            null === importStatusValue
                                ? `supplier_portal.product_file_dropping.supplier_files.status.all`
                                : `supplier_portal.product_file_dropping.supplier_files.status.${importStatusValue}`
                        )}
                    </SwitcherButton>
                    {isDropdownOpen && (
                        <Dropdown.Overlay verticalPosition="down" onClose={close}>
                            <Dropdown.ItemCollection>
                                <Dropdown.Item key="all" onClick={() => onImportStatusChange(null)}>
                                    {translate('supplier_portal.product_file_dropping.supplier_files.status.all')}
                                </Dropdown.Item>
                                {Object.values(ImportStatus).map(importStatus => (
                                    <Dropdown.Item
                                        key={importStatus}
                                        onClick={() => onImportStatusChange(importStatus)}
                                    >
                                        {translate(
                                            `supplier_portal.product_file_dropping.supplier_files.status.${importStatus}`
                                        )}
                                    </Dropdown.Item>
                                ))}
                            </Dropdown.ItemCollection>
                        </Dropdown.Overlay>
                    )}
                </Dropdown>
            </StyledSearch>

            {(0 < productFiles.length || '' !== searchValue || null !== importStatusValue) && (
                <>
                    {0 < productFiles.length && (
                        <Pagination
                            followPage={onChangePage}
                            currentPage={
                                currentPage > totalSearchResults / PRODUCT_FILES_PER_PAGE
                                    ? Math.ceil(totalSearchResults / PRODUCT_FILES_PER_PAGE)
                                    : currentPage
                            }
                            totalItems={totalSearchResults}
                            itemsPerPage={PRODUCT_FILES_PER_PAGE}
                        />
                    )}

                    {0 === productFiles.length && (
                        <EmptyProductFilesList message="supplier_portal.product_file_dropping.supplier_files.search.no_results" />
                    )}

                    {0 < productFiles.length && (
                        <Table>
                            <Table.Header>
                                <Table.HeaderCell>
                                    {translate(
                                        'supplier_portal.product_file_dropping.supplier_files.columns.upload_date'
                                    )}
                                </Table.HeaderCell>
                                <Table.HeaderCell>
                                    {translate('supplier_portal.product_file_dropping.supplier_files.columns.filename')}
                                </Table.HeaderCell>
                                <Table.HeaderCell>
                                    {translate(
                                        'supplier_portal.product_file_dropping.supplier_files.columns.contributor'
                                    )}
                                </Table.HeaderCell>
                                {displaySupplierColumn && (
                                    <Table.HeaderCell>
                                        {translate(
                                            'supplier_portal.product_file_dropping.supplier_files.columns.supplier'
                                        )}
                                    </Table.HeaderCell>
                                )}
                                <Table.HeaderCell>
                                    {translate('supplier_portal.product_file_dropping.supplier_files.columns.status')}
                                </Table.HeaderCell>
                                <Table.HeaderCell></Table.HeaderCell>
                                <Table.HeaderCell></Table.HeaderCell>
                            </Table.Header>
                            <Table.Body>
                                {productFiles.map((productFile: ProductFileRow) => {
                                    const uploadedDate = dateFormatter(productFile.uploadedAt, {
                                        day: '2-digit',
                                        hour: '2-digit',
                                        minute: '2-digit',
                                        month: '2-digit',
                                        year: 'numeric',
                                    });
                                    return (
                                        <Table.Row
                                            key={productFile.identifier}
                                            onClick={() => goToProductFile(productFile.identifier)}
                                        >
                                            <Table.Cell>{uploadedDate}</Table.Cell>
                                            <Table.Cell>
                                                <FilenameCell>{productFile.filename}</FilenameCell>
                                            </Table.Cell>
                                            <Table.Cell>{productFile.contributor}</Table.Cell>
                                            {displaySupplierColumn && (
                                                <Table.Cell>
                                                    {productFile.hasOwnProperty('supplier') && productFile.supplier}
                                                </Table.Cell>
                                            )}
                                            <Table.Cell>
                                                <ProductFileImportStatus importStatus={productFile.importStatus} />
                                            </Table.Cell>
                                            <HasUnreadCommentsCell>
                                                {productFile.hasUnreadComments && (
                                                    <StyledPill data-testid="unread-comments-pill" level="primary" />
                                                )}
                                            </HasUnreadCommentsCell>
                                            <DownloadCell>
                                                <StyledIconButton
                                                    data-testid="Download icon"
                                                    icon={<StyledDownloadIcon animateOnHover={true} />}
                                                    title={translate(
                                                        'supplier_portal.product_file_dropping.supplier_files.columns.download'
                                                    )}
                                                    ghost={'borderless'}
                                                    onClick={(event: any) => event.stopPropagation()}
                                                    href={router.generate('supplier_portal_retailer_download_file', {
                                                        productFileIdentifier: productFile.identifier,
                                                    })}
                                                />
                                            </DownloadCell>
                                        </Table.Row>
                                    );
                                })}
                            </Table.Body>
                        </Table>
                    )}
                </>
            )}
        </>
    );
};

const StyledPipe = styled.div`
    height: 24px;
    border-right: 1px solid ${getColor('grey100')};
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

const DownloadCell = styled(Table.ActionCell)`
    width: 50px;
`;

const StyledPill = styled(Pill)`
    background-color: ${getColor('blue100')};
    flex-direction: row-reverse;
`;

const HasUnreadCommentsCell = styled(Table.Cell)`
    width: 10px;
`;

const FilenameCell = styled.span`
    text-overflow: ellipsis;
    overflow: hidden;
`;

const StyledSearch = styled(Search)`
    margin-bottom: 10px;
`;

export {ProductFilesList};
