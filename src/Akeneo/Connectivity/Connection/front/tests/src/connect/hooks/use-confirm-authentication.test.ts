import {useConfirmAuthentication} from '@src/connect/hooks/use-confirm-authentication';
import {renderHook} from '@testing-library/react-hooks';
import fetchMock from 'jest-fetch-mock';
import {mockFetchResponses} from '../../../test-utils';

beforeEach(() => {
    fetchMock.resetMocks();
});

test('it confirms the authentication', async () => {
    const expectedData = {
        redirectUrl: 'a_redirect_url',
    };
    mockFetchResponses({
        'akeneo_connectivity_connection_apps_rest_confirm_authentication?clientId=a_cliend_id': {
            json: expectedData,
        },
    });

    const {result} = renderHook(() => useConfirmAuthentication('a_cliend_id'));
    const data = await result.current();

    expect(fetchMock).toBeCalledWith(
        'akeneo_connectivity_connection_apps_rest_confirm_authentication?clientId=a_cliend_id',
        {
            method: 'POST',
            headers: [['X-Requested-With', 'XMLHttpRequest']],
        }
    );
    expect(data).toStrictEqual(expectedData);
});
