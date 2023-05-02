require('jest-fetch-mock').enableMocks();
import fetchMock from 'jest-fetch-mock';
import {systemConfiguration} from './system-configuration';

test('it fetches sandbox banner display boolean', async () => {
  fetchMock.doMock(() =>
    Promise.resolve(
      JSON.stringify({
        pim_ui___sandbox_banner: {value: '0'},
      })
    )
  );

  await systemConfiguration.initialize();

  expect(systemConfiguration.get('sandbox_banner')).toBe(false);

  await systemConfiguration.refresh();

  expect(fetchMock).toHaveBeenNthCalledWith(2, '/system/rest');
});

test('it returns default value if something wrong happens', async () => {
  fetchMock.mockResponseOnce(JSON.stringify('Unauthorized'), {status: 403});

  await systemConfiguration.initialize();

  expect(systemConfiguration.get('sandbox_banner')).toBe(true);
});
