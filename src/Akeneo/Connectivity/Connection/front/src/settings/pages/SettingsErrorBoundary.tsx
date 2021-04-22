import React, {Component} from 'react';
import {PageContent, PageHeader, RuntimeError} from '../../common/components';
import {Translate} from '../../shared/translate';
import {Breadcrumb} from 'akeneo-design-system';
import {UserButtons} from '../../shared/user';
import {useRouter} from '../../shared/router/use-router';

const SettingsBreadcrumb = () => {
    const generateUrl = useRouter();

    return (
        <Breadcrumb>
            <Breadcrumb.Step href={`#${generateUrl('akeneo_connectivity_connection_audit_index')}`}>
                <Translate id='pim_menu.tab.connect' />
            </Breadcrumb.Step>
            <Breadcrumb.Step>
                <Translate id='pim_menu.item.connect_connection_settings' />
            </Breadcrumb.Step>
        </Breadcrumb>
    );
};

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
                    <PageHeader breadcrumb={<SettingsBreadcrumb />} userButtons={<UserButtons />} />

                    <PageContent>
                        <RuntimeError />
                    </PageContent>
                </>
            );
        }

        return this.props.children;
    }
}
