import React, {Component} from 'react';
import {Breadcrumb, BreadcrumbItem, PageContent, PageHeader, RuntimeError} from '../../common/components';
import {PimView} from '../../infrastructure/pim-view/PimView';
import {BreadcrumbRouterLink} from '../../shared/router';
import {Translate} from '../../shared/translate';

export class SettingsErrorBoundary extends Component<unknown, {hasError: boolean}> {
    constructor(props: unknown) {
        super(props);
        this.state = {hasError: false};
    }

    static getDerivedStateFromError() {
        return {hasError: true};
    }

    render() {
        if (this.state.hasError) {
            return (
                <>
                    <PageHeader
                        breadcrumb={
                            <Breadcrumb>
                                <BreadcrumbRouterLink route={'oro_config_configuration_system'}>
                                    <Translate id='pim_menu.tab.system' />
                                </BreadcrumbRouterLink>
                                <BreadcrumbItem onClick={() => undefined} isLast={false}>
                                    <Translate id='pim_menu.item.connection_settings' />
                                </BreadcrumbItem>
                            </Breadcrumb>
                        }
                        userButtons={
                            <PimView
                                className='AknTitleContainer-userMenuContainer AknTitleContainer-userMenu'
                                viewName='pim-connectivity-connection-user-navigation'
                            />
                        }
                    />

                    <PageContent>
                        <RuntimeError />
                    </PageContent>
                </>
            );
        }

        return this.props.children;
    }
}
