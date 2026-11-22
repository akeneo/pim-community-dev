require('jest-fetch-mock').enableMocks();
import fetchMock from 'jest-fetch-mock';
import {userContext} from './user-context';

beforeEach(() => fetchMock.resetMocks());

test('it loads the user context and exposes locales and scope', async () => {
  fetchMock.mockResponseOnce(
    JSON.stringify({
      username: 'julia',
      user_default_locale: 'fr_FR',
      catalog_default_locale: 'en_US',
      catalog_default_scope: 'ecommerce',
    })
  );

  await userContext.initialize();

  expect(fetchMock.mock.calls[0][0]).toBe('/rest/user/');
  expect(userContext.get('username')).toBe('julia');
  expect(userContext.get('uiLocale')).toBe('fr_FR');
  expect(userContext.get('catalogLocale')).toBe('en_US');
  expect(userContext.get('catalogScope')).toBe('ecommerce');

  fetchMock.mockResponseOnce(JSON.stringify({username: 'peter', user_default_locale: 'de_DE'}));
  await userContext.refresh();
  expect(userContext.get('username')).toBe('peter');
  expect(userContext.get('uiLocale')).toBe('de_DE');
});

test('it triggers a change event when a value is set', () => {
  const callback = jest.fn();
  userContext.on('change:catalogLocale', callback);

  userContext.set('catalogLocale', 'fr_FR');
  userContext.set('catalogLocale', 'de_DE', {silent: true});

  expect(callback).toHaveBeenCalledTimes(1);
  expect(userContext.get('catalogLocale')).toBe('de_DE');
  userContext.off();
});
