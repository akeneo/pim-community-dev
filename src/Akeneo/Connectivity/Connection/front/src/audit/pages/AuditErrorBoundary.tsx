import React, {Component} from 'react';
import {Breadcrumb, PageContent, PageHeader, RuntimeError} from '../../common/components';
import {PimView} from '../../infrastructure/pim-view/PimView';
import {BreadcrumbRouterLink} from '../../shared/router';
import {Translate} from '../../shared/translate';

export class AuditErrorBoundary extends Component<unknown, {hasError: boolean}> {
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
                                <BreadcrumbRouterLink route={'pim_dashboard_index'} isLast={false}>
                                    <Translate id='pim_menu.tab.activity' />
                                </BreadcrumbRouterLink>
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
