import {useFetchAppWizardData} from '@src/connect/hooks/use-fetch-app-wizard-data';
import {renderHook} from '@testing-library/react-hooks';
import fetchMock from 'jest-fetch-mock';
import {mockFetchResponses} from '../../../test-utils';

beforeEach(() => {
    fetchMock.resetMocks();
});

test('it fetchs the wizard data', async () => {
    const expectedData = {
        appName: 'a_name',
        appLogo: 'a_logo',
        scopeMessages: [
            {
                icon: 'an_icon',
                type: 'a_type',
                entities: 'some_entities',
            },
        ],
        authenticationScopes: ['email', 'profile'],
    };

    mockFetchResponses({
        'akeneo_connectivity_connection_apps_rest_get_wizard_data?clientId=a_cliend_id': {
            json: expectedData,
        },
    });

    const {result} = renderHook(() => useFetchAppWizardData('a_cliend_id'));
    const data = await result.current();

    expect(fetchMock).toBeCalledWith('akeneo_connectivity_connection_apps_rest_get_wizard_data?clientId=a_cliend_id', {
        method: 'GET',
        headers: [['X-Requested-With', 'XMLHttpRequest']],
    });
    expect(data).toStrictEqual(expectedData);
});
