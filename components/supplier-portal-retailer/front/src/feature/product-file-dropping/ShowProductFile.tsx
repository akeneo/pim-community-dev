import React from 'react';
import {Breadcrumb, ImportXlsxIllustration, Pill, TabBar, useTabBar, onboarderTheme} from 'akeneo-design-system';
import {PageContent, PageHeader, PimView, useTranslate} from '@akeneo-pim-community/shared';
import {useHistory, useParams} from 'react-router';
import {Discussion, GeneralInformation, ProductFileImportConfigurationsModal} from './components';
import {useProductFile} from './hooks/useProductFile';
import styled from 'styled-components';
import {ProductFileImportStatus} from './components/ProductFileImportStatus';
import {ProductFilePreview} from './components/ProductFilePreview';

const ShowProductFile = () => {
    const translate = useTranslate();
    const history = useHistory();
    const [isCurrent, switchTo] = useTabBar('discussion');
    const {productFileIdentifier} = useParams<{productFileIdentifier: string}>();
    const [productFile, saveComment, validationError] = useProductFile(productFileIdentifier);

    if (null === productFile) {
        return null;
    }

    return (
        <>
            <PageHeader>
                <PageHeader.Illustration>
                    <ImportXlsxIllustration size={140} />
                </PageHeader.Illustration>
                <PageHeader.Breadcrumb>
                    <Breadcrumb>
                        <Breadcrumb.Step href={history.createHref({pathname: '/product-file/'})}>
                            {translate('supplier_portal.product_file_dropping.breadcrumb.root')}
                        </Breadcrumb.Step>
                        <Breadcrumb.Step href={history.createHref({pathname: '/product-file/'})}>
                            {translate('supplier_portal.product_file_dropping.breadcrumb.product_files')}
                        </Breadcrumb.Step>
                        <Breadcrumb.Step>{productFile.originalFilename}</Breadcrumb.Step>
                    </Breadcrumb>
                </PageHeader.Breadcrumb>
                <PageHeader.UserActions>
                    <PimView
                        viewName="pim-menu-user-navigation"
                        className="AknTitleContainer-userMenuContainer AknTitleContainer-userMenu"
                    />
                </PageHeader.UserActions>
                <PageHeader.Actions>
                    <ProductFileImportConfigurationsModal productFileIdentifier={productFile.identifier} />
                </PageHeader.Actions>
                <PageHeader.Title>{productFile.originalFilename}</PageHeader.Title>
                <PageHeader.Content>
                    <StatusContainer>
                        <StatusLabel>Status:&nbsp;</StatusLabel>
                        <ProductFileImportStatus importStatus={productFile.importStatus} />
                    </StatusContainer>
                </PageHeader.Content>
            </PageHeader>
            <StyledPageContent>
                <TabBar moreButtonTitle="More">
                    <TabBar.Tab isActive={isCurrent('discussion')} onClick={() => switchTo('discussion')}>
                        {translate('supplier_portal.product_file_dropping.supplier_files.tabs.discussion')}
                        {productFile.hasUnreadComments && <StyledPill level="primary" />}
                    </TabBar.Tab>
                    <TabBar.Tab isActive={isCurrent('preview')} onClick={() => switchTo('preview')}>
                        {translate('supplier_portal.product_file_dropping.supplier_files.tabs.preview')}
                    </TabBar.Tab>
                    <TabBar.Tab
                        isActive={isCurrent('general_information')}
                        onClick={() => switchTo('general_information')}
                    >
                        {translate('supplier_portal.product_file_dropping.supplier_files.tabs.general_information')}
                    </TabBar.Tab>
                </TabBar>
                {isCurrent('discussion') && (
                    <Discussion productFile={productFile} saveComment={saveComment} validationError={validationError} />
                )}
                {isCurrent('general_information') && <GeneralInformation productFile={productFile} />}
                {isCurrent('preview') && <ProductFilePreview productFileIdentifier={productFile.identifier} />}
            </StyledPageContent>
        </>
    );
};

const StatusContainer = styled.div`
    margin-top: 15px;
`;

const StatusLabel = styled.span`
    font-weight: bold;
`;

const StyledPill = styled(Pill)`
    background-color: ${onboarderTheme.color.brand100};
`;

const StyledPageContent = styled(PageContent)`
    height: calc(100vh - 200px);
`;

export {ShowProductFile};
