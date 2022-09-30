import React from 'react';
import {Breadcrumb, Button, ImportXlsxIllustration, TabBar, useTabBar} from 'akeneo-design-system';
import {PageContent, PageHeader, PimView, useTranslate} from '@akeneo-pim-community/shared';
import {useHistory, useParams} from 'react-router';
import {Discussion} from './components';
import {useProductFile} from './hooks/useProductFile';
import styled from 'styled-components';

const StatusContainer = styled.div`
    margin-top: 15px;
`;

const StatusLabel = styled.span`
    font-weight: bold;
`;

const ShowProductFile = () => {
    const translate = useTranslate();
    const history = useHistory();
    const [isCurrent, switchTo] = useTabBar('discussion');
    const {productFileIdentifier} = useParams<{productFileIdentifier: string}>();
    const [productFile, saveComment, validationErrors] = useProductFile(productFileIdentifier);

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
                        <Breadcrumb.Step href={history.createHref({pathname: '/product-file-dropping'})}>
                            {translate('supplier_portal.product_file_dropping.breadcrumb.root')}
                        </Breadcrumb.Step>
                        <Breadcrumb.Step href={history.createHref({pathname: '/product-file-dropping'})}>
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
                    <Button level={'primary'}>
                        {translate(
                            'supplier_portal.product_file_dropping.supplier_files.discussion.import_file_button_label'
                        )}
                    </Button>
                </PageHeader.Actions>
                <PageHeader.Title>{productFile.originalFilename}</PageHeader.Title>
                <PageHeader.Content>
                    <StatusContainer>
                        <StatusLabel>Status:&nbsp;</StatusLabel>
                        <span>Completed (@todo)</span>
                    </StatusContainer>
                </PageHeader.Content>
            </PageHeader>
            <PageContent>
                <TabBar moreButtonTitle="More">
                    <TabBar.Tab isActive={isCurrent('discussion')} onClick={() => switchTo('discussion')}>
                        {translate('supplier_portal.product_file_dropping.supplier_files.tabs.discussion')}
                    </TabBar.Tab>
                </TabBar>
                {isCurrent('discussion') && (
                    <Discussion
                        productFile={productFile}
                        saveComment={saveComment}
                        validationErrors={validationErrors}
                    />
                )}
            </PageContent>
        </>
    );
};

export {ShowProductFile};
