import React, {useEffect, useState} from 'react';
import {Breadcrumb, useDebounce} from 'akeneo-design-system';
import {PageContent, PageHeader, PimView, useTranslate} from '@akeneo-pim-community/shared';
import styled from 'styled-components';
import {useProductFiles} from './hooks';
import {EmptyProductFilesList, ProductFilesList} from './components';
import {useHistory} from 'react-router';

const ListProductFiles = () => {
    const translate = useTranslate();
    const [page, setPage] = useState<number>(1);
    const [searchValue, setSearchValue] = useState('');
    const [importStatusValue, setImportStatusValue] = useState<null | string>(null);
    const debouncedSearchValue = useDebounce(searchValue);
    const [productFiles, totalProductFiles, totalSearchResults] = useProductFiles(
        page,
        debouncedSearchValue,
        setPage,
        importStatusValue
    );
    const history = useHistory();

    useEffect(() => {
        0 < totalSearchResults && setPage(1);
    }, [totalSearchResults, searchValue, importStatusValue]);

    return (
        <>
            <PageHeader>
                <PageHeader.Breadcrumb>
                    <Breadcrumb>
                        <Breadcrumb.Step href={history.createHref({pathname: '/product-file/'})}>
                            {translate('supplier_portal.product_file_dropping.breadcrumb.root')}
                        </Breadcrumb.Step>
                        <Breadcrumb.Step>
                            {translate('supplier_portal.product_file_dropping.breadcrumb.product_files')}
                        </Breadcrumb.Step>
                    </Breadcrumb>
                </PageHeader.Breadcrumb>
                <PageHeader.UserActions>
                    <PimView
                        viewName="pim-menu-user-navigation"
                        className="AknTitleContainer-userMenuContainer AknTitleContainer-userMenu"
                    />
                </PageHeader.UserActions>
                <PageHeader.Title>
                    {translate(
                        'supplier_portal.product_file_dropping.supplier_files.title',
                        {count: totalProductFiles},
                        totalProductFiles
                    )}
                </PageHeader.Title>
            </PageHeader>
            <StyledPageContent>
                {0 === totalSearchResults && '' === searchValue && null === importStatusValue ? (
                    <EmptyProductFilesList message="supplier_portal.product_file_dropping.supplier_files.no_files" />
                ) : (
                    <ProductFilesList
                        productFiles={productFiles}
                        totalSearchResults={totalSearchResults}
                        currentPage={page}
                        onChangePage={setPage}
                        searchValue={searchValue}
                        onSearch={setSearchValue}
                        importStatusValue={importStatusValue}
                        handleImportStatusChange={setImportStatusValue}
                    />
                )}
            </StyledPageContent>
        </>
    );
};

const StyledPageContent = styled(PageContent)`
    display: flex;
    flex-direction: column;
`;

export {ListProductFiles};
