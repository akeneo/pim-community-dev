import React, {Component} from 'react';
import {PageContent, PageHeader, RuntimeError} from '../../common/components';
import {useRoute} from '../../shared/router';
import {Translate} from '../../shared/translate';
import {Breadcrumb} from 'akeneo-design-system';
import {UserButtons} from '../../shared/user';

const AuditBreadcrumb = () => {
    const dashboardHref = `#${useRoute('pim_dashboard_index')}`;

    return (
        <Breadcrumb>
            <Breadcrumb.Step href={dashboardHref}>
                <Translate id='pim_menu.tab.activity' />
            </Breadcrumb.Step>
            <Breadcrumb.Step>
                <Translate id='pim_menu.item.connection_audit' />
            </Breadcrumb.Step>
        </Breadcrumb>
    );
};

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
                    <PageHeader breadcrumb={<AuditBreadcrumb />} userButtons={<UserButtons />} />

                    <PageContent>
                        <RuntimeError />
                    </PageContent>
                </>
            );
        }

        return this.props.children;
    }
}
