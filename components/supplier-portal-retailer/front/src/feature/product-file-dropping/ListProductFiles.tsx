import React, {useEffect, useState} from 'react';
import {Breadcrumb} from 'akeneo-design-system';
import {PageContent, PageHeader, PimView, useTranslate} from '@akeneo-pim-community/shared';
import styled from 'styled-components';
import {useProductFiles} from './hooks';
import {ProductFilesList} from './components';
import {useHistory} from 'react-router';

const ListProductFiles = () => {
    const translate = useTranslate();
    const [page, setPage] = useState<number>(1);
    const [productFiles, totalProductFiles] = useProductFiles(page);
    const history = useHistory();

    useEffect(() => {
        0 < totalProductFiles && setPage(1);
    }, [totalProductFiles]);

    return (
        <>
            <PageHeader>
                <PageHeader.Breadcrumb>
                    <Breadcrumb>
                        <Breadcrumb.Step href={history.createHref({pathname: '/product-file-dropping'})}>
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
                <ProductFilesList
                    productFiles={productFiles}
                    totalProductFiles={totalProductFiles}
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

export {ListProductFiles};
