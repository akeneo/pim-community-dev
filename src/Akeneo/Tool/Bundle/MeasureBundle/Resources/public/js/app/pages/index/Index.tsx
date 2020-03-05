import React, {useContext} from 'react';
import {PageHeader} from 'akeneomeasure/js/app/shared/components/PageHeader';
import {PageContent} from 'akeneomeasure/js/app/shared/components/PageContent';
import {PimView} from 'akeneomeasure/js/app/shared/legacy/pim-view/PimView';
import {Breadcrumb} from 'akeneomeasure/js/app/shared/components/Breadcrumb';
import {BreadcrumbItem} from 'akeneomeasure/js/app/shared/components/BreadcrumbItem';
import {TranslateContext} from 'akeneomeasure/js/app/shared/translate/translate-context';

export const Index = () => {
    const __ = useContext(TranslateContext);

    return (
        <>
            <PageHeader
                userButtons={
                    <PimView
                        className='AknTitleContainer-userMenuContainer AknTitleContainer-userMenu'
                        viewName='pim-connectivity-connection-user-navigation'
                    />
                }
                breadcrumb={
                    <Breadcrumb>
                        <BreadcrumbItem>
                            {__('pim_menu.tab.settings')}
                        </BreadcrumbItem>
                        <BreadcrumbItem>
                            {__('pim_menu.item.measurements')}
                        </BreadcrumbItem>
                    </Breadcrumb>
                }
            >
                {__('measurements.families', {itemsCount: '0'}, 0)}
            </PageHeader>

            <PageContent>{/* TODO */}</PageContent>
        </>
    );
};
