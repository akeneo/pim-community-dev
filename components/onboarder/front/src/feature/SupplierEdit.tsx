import React, {useCallback} from 'react';
import {
    Breadcrumb,
    Button,
    Dropdown,
    IconButton,
    MoreIcon,
    TabBar,
    useBooleanState,
    useTabBar,
} from 'akeneo-design-system';
import {PageContent, PageHeader, PimView, UnsavedChanges, useTranslate} from '@akeneo-pim-community/shared';
import styled from 'styled-components';
import {Configuration} from './components/SupplierEdit/Configuration';
import {useSupplier} from './hooks';
import {useHistory, useParams} from 'react-router';
import {ContributorList} from './components/SupplierEdit/ContributorList';
import {ContributorEmail} from './models';
import {DeleteSupplier} from './components/DeleteSupplier';

const SupplierEdit = () => {
    const translate = useTranslate();
    const [isCurrent, switchTo] = useTabBar('configuration');
    const {supplierIdentifier} = useParams<{supplierIdentifier: string}>();
    const [supplier, handleSupplierChanges, supplierHasChanges, saveSupplier] = useSupplier(supplierIdentifier);
    const history = useHistory();

    const handleSupplierLabelChange = useCallback(
        (newLabel: string) => {
            handleSupplierChanges(supplier => {
                if (supplier === null) return null;

                return {...supplier, label: newLabel};
            });
        },
        [handleSupplierChanges]
    );

    const handleSupplierContributorsChange = useCallback(
        (newContributors: ContributorEmail[]) => {
            handleSupplierChanges(supplier => {
                if (supplier === null) return null;

                return {...supplier, contributors: newContributors};
            });
        },
        [handleSupplierChanges]
    );

    if (supplier === null) {
        return null;
    }

    return (
        <Container>
            <PageHeader>
                <PageHeader.Breadcrumb>
                    <Breadcrumb>
                        <Breadcrumb.Step>{translate('onboarder.supplier.breadcrumb.root')}</Breadcrumb.Step>
                        <Breadcrumb.Step href={history.createHref({pathname: '/'})}>
                            {translate('onboarder.supplier.breadcrumb.suppliers')}
                        </Breadcrumb.Step>
                        <Breadcrumb.Step>{supplier.label}</Breadcrumb.Step>
                    </Breadcrumb>
                </PageHeader.Breadcrumb>
                <PageHeader.UserActions>
                    <PimView
                        viewName="pim-menu-user-navigation"
                        className="AknTitleContainer-userMenuContainer AknTitleContainer-userMenu"
                    />
                </PageHeader.UserActions>
                <PageHeader.Actions>
                    <SecondaryActions supplierIdentifier={supplierIdentifier} />
                    <Button level={'primary'} onClick={saveSupplier}>
                        {translate('pim_common.save')}
                    </Button>
                </PageHeader.Actions>
                <PageHeader.Title>{supplier.label}</PageHeader.Title>
                <PageHeader.State>{supplierHasChanges && <UnsavedChanges />}</PageHeader.State>
            </PageHeader>
            <StyledPageContent>
                <TabBar moreButtonTitle="More">
                    <TabBar.Tab isActive={isCurrent('configuration')} onClick={() => switchTo('configuration')}>
                        {translate('onboarder.supplier.supplier_edit.tabs.configuration')}
                    </TabBar.Tab>
                    <TabBar.Tab isActive={isCurrent('contributors')} onClick={() => switchTo('contributors')}>
                        {translate('onboarder.supplier.supplier_edit.tabs.contributors')}
                    </TabBar.Tab>
                    <TabBar.Tab isActive={isCurrent('product_files')} onClick={() => switchTo('product_files')}>
                        {translate('onboarder.supplier.supplier_edit.tabs.product_files')}
                    </TabBar.Tab>
                </TabBar>
                {isCurrent('configuration') && (
                    <Configuration supplier={supplier} setLabel={handleSupplierLabelChange} />
                )}
                {isCurrent('contributors') && (
                    <ContributorList
                        contributors={supplier.contributors}
                        setContributors={handleSupplierContributorsChange}
                    />
                )}
            </StyledPageContent>
        </Container>
    );
};

type SecondaryActionsProps = {
    supplierIdentifier: string;
};

const SecondaryActions = ({supplierIdentifier}: SecondaryActionsProps) => {
    const translate = useTranslate();
    const [isDropdownOpen, openDropdown, closeDropdown] = useBooleanState();
    const [isModalOpen, openModal, closeModal] = useBooleanState(false);
    const history = useHistory();

    const onSupplierDeleted = () => {
        closeModal();
        history.push('/');
    };

    return (
        <>
            <Dropdown>
                <IconButton
                    title={translate('pim_common.other_actions')}
                    icon={<MoreIcon />}
                    level="tertiary"
                    ghost="borderless"
                    onClick={openDropdown}
                />
                {isDropdownOpen && (
                    <Dropdown.Overlay onClose={closeDropdown}>
                        <Dropdown.Header>
                            <Dropdown.Title>{translate('pim_common.other_actions')}</Dropdown.Title>
                        </Dropdown.Header>
                        <Dropdown.ItemCollection>
                            <Dropdown.Item
                                onClick={() => {
                                    openModal();
                                    closeDropdown();
                                }}
                            >
                                {translate('onboarder.supplier.supplier_edit.delete_label')}
                            </Dropdown.Item>
                        </Dropdown.ItemCollection>
                    </Dropdown.Overlay>
                )}
            </Dropdown>
            {isModalOpen && (
                <DeleteSupplier
                    identifier={supplierIdentifier}
                    onSupplierDeleted={onSupplierDeleted}
                    onCloseModal={closeModal}
                />
            )}
        </>
    );
};

const Container = styled.div``;

const StyledPageContent = styled(PageContent)`
    display: flex;
    flex-direction: column;
`;

export {SupplierEdit};
