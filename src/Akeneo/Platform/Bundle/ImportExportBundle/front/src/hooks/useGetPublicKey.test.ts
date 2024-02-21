import {renderHookWithProviders} from '@akeneo-pim-community/shared';
import {useGetPublicKey} from './useGetPublicKey';

test('it returns a public key', async () => {
  global.fetch = jest.fn().mockImplementation(async () => ({
    ok: true,
    json: async () => '-----BEGIN CERTIFICATE-----publickey-----END CERTIFICATE-----',
  }));

  const {result, waitForNextUpdate} = renderHookWithProviders(() => useGetPublicKey());

  await waitForNextUpdate();

  const expectedPublicKey = '-----BEGIN CERTIFICATE-----publickey-----END CERTIFICATE-----';
  expect(result.current).toEqual(expectedPublicKey);

  expect(global.fetch).toBeCalledWith('pimee_job_automation_get_public_key', {
    headers: {
      'Content-Type': 'application/json',
      'X-Requested-With': 'XMLHttpRequest',
    },
    method: 'GET',
  });
});
