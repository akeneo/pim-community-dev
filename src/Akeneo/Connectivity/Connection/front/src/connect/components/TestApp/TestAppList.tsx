import React, {FC} from 'react';
import {TestApp, TestApps} from '../../../model/app';
import {Section} from '../Section';
import {useTranslate} from '../../../shared/translate';
import {TestAppCard} from './TestAppCard';
import {ActivateAppButton} from '../ActivateAppButton';
import {useSecurity} from '../../../shared/security';

interface Props {
    testApps: TestApps;
}

export const TestAppList: FC<Props> = ({testApps}) => {
    const security = useSecurity();
    const translate = useTranslate();

    if (testApps.total <= 0) {
        return null;
    }

    const testAppsList = testApps.apps.map((testApp: TestApp) => (
        <TestAppCard
            key={testApp.id}
            testApp={testApp}
            additionalActions={[
                <ActivateAppButton
                    key={1}
                    id={testApp.id}
                    isConnected={testApp.connected}
                    isDisabled={!security.isGranted('akeneo_connectivity_connection_manage_apps')}
                    isPending={false}
                />,
            ]}
        />
    ));

    return (
        <Section
            title={translate('akeneo_connectivity.connection.connect.marketplace.test_apps.title')}
            information={translate(
                'akeneo_connectivity.connection.connect.marketplace.apps.total',
                {
                    total: testApps.total.toString(),
                },
                testApps.total
            )}
            emptyMessage={translate('akeneo_connectivity.connection.connect.marketplace.apps.empty')}
        >
            {testAppsList}
        </Section>
    );
};
