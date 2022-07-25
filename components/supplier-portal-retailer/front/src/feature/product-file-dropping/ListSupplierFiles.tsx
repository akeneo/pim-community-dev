import React, {useEffect, useState} from 'react';
import {Breadcrumb} from 'akeneo-design-system';
import {PageContent, PageHeader, PimView, useTranslate} from '@akeneo-pim-community/shared';
import styled from 'styled-components';
import {useSupplierFiles} from './hooks';
import {SupplierFilesList} from './components/SupplierFilesList';

const ListSupplierFiles = () => {
    const translate = useTranslate();
    const [page, setPage] = useState<number>(1);
    const [supplierFiles, totalSupplierFiles] = useSupplierFiles(page);

    useEffect(() => {
        0 < totalSupplierFiles && setPage(1);
    }, [totalSupplierFiles]);

    return (
        <>
            <PageHeader>
                <PageHeader.Breadcrumb>
                    <Breadcrumb>
                        <Breadcrumb.Step>
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
                        {count: totalSupplierFiles},
                        totalSupplierFiles
                    )}
                </PageHeader.Title>
            </PageHeader>
            <StyledPageContent>
                <SupplierFilesList
                    supplierFiles={supplierFiles}
                    totalSupplierFiles={totalSupplierFiles}
                    currentPage={page}
                    onChangePage={setPage}
                />
            </StyledPageContent>
        </>
    );
};

const StyledPageContent = styled(PageContent)`
    display: flex;
    flex-direction: column;
`;

export {ListSupplierFiles};
