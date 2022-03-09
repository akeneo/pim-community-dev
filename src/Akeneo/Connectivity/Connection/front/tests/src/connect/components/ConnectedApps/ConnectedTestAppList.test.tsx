import React from 'react';
import {screen} from '@testing-library/react';
import '@testing-library/jest-dom/extend-expect';
import {ConnectedAppCard} from '@src/connect/components/ConnectedApps/ConnectedAppCard';
import {renderWithProviders} from '../../../../test-utils';
import {useTranslate} from '@src/shared/translate';
import {useAppDeveloperMode} from '@src/connect/hooks/use-app-developer-mode';
import {SectionTitle} from 'akeneo-design-system';
import {CardGrid} from '@src/connect/components/Section';

const {ConnectedTestAppList} = jest.requireActual('@src/connect/components/ConnectedApps/ConnectedTestAppList');

(useTranslate as jest.Mock).mockImplementation(() => (id: string) => id);
(SectionTitle as unknown as jest.Mock).mockImplementation(({children}) => <>{children}</>);
(CardGrid.render as unknown as jest.Mock).mockImplementation(({children}) => <section>{children}</section>);
(ConnectedAppCard as unknown as jest.Mock).mockImplementation(({item}) => <div>{item.name}</div>);

jest.unmock('react');
jest.unmock('history');
jest.unmock('styled-components');
jest.unmock('react-router-dom');
jest.unmock('@testing-library/react');
jest.unmock('@testing-library/jest-dom/extend-expect');
jest.unmock('../../../../test-utils');

const connectedTestApps = [
    {
        id: 'test_id_a',
        name: 'App A',
        scopes: [],
        connection_code: 'connectionCodeA',
        logo: 'http://www.example.test/path/to/logo/a',
        author: 'author A',
        user_group_name: 'user_group_a',
        categories: ['category A1', 'category A2'],
        certified: false,
        partner: 'partner A',
        activate_url: 'http://www.example.com/activate',
        is_test_app: true,
    },
    {
        id: 'test_id_b',
        name: 'App B',
        scopes: [],
        connection_code: 'connectionCodeB',
        logo: 'http://www.example.test/path/to/logo/b',
        author: 'author B',
        user_group_name: 'user_group_b',
        categories: [],
        certified: false,
        partner: 'partner B',
        activate_url: 'http://www.example.com/activate',
        is_test_app: true,
    },
];

test('it renders list of connected apps', () => {
    // wrong mocked hook, should mock useFeatureFlag
    (useAppDeveloperMode as jest.Mock).mockReturnValue(true);

    renderWithProviders(<ConnectedTestAppList connectedTestApps={connectedTestApps} />);

    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.connected_apps.list.test_apps.title')
    ).toBeInTheDocument();
    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.connected_apps.list.apps.total', {exact: false})
    ).toBeInTheDocument();
    expect(screen.queryByText(connectedTestApps[0].name)).toBeInTheDocument();
    expect(screen.queryByText(connectedTestApps[1].name)).toBeInTheDocument();
});

test('it does not render if list is empty', () => {
    renderWithProviders(<ConnectedTestAppList connectedTestApps={[]} />);

    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.connected_apps.list.test_apps.title')
    ).not.toBeInTheDocument();

    expect(ConnectedAppCard).not.toHaveBeenCalled();
});

test('it does not render if feature flag is disabled', () => {
    // wrong mocked hook, should mock useFeatureFlag
    (useAppDeveloperMode as jest.Mock).mockReturnValue(false);

    renderWithProviders(<ConnectedTestAppList connectedTestApps={connectedTestApps} />);

    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.connected_apps.list.test_apps.title')
    ).not.toBeInTheDocument();

    expect(ConnectedAppCard).not.toHaveBeenCalled();
});
