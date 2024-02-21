require('jest-fetch-mock').enableMocks();
const userContext = {
  get: jest.fn(),
};
jest.mock('./user-context', () => ({
  userContext: userContext,
}));

import fetchMock from 'jest-fetch-mock';
import {initTranslator} from './init-translator';
// @ts-ignore
import Translator from '../../legacy/translator';

test('It fetch translations based on the user locale', async () => {
  userContext.get.mockReturnValue('en_US');
  fetchMock.doMock(() =>
    Promise.resolve(
      JSON.stringify({
        locale: 'en_US',
        defaultDomains: ['jsmessages', 'validators'],
        messages: {},
      })
    )
  );

  await initTranslator.fetch();
  expect(fetchMock).toHaveBeenNthCalledWith(1, 'js/translation/en_US.js');
  expect(Translator.locale).toEqual('en_US');
  expect(Translator.defaultDomains).toEqual(['jsmessages', 'validators']);
});

test('It fetch translations only once', async () => {
  userContext.get.mockReturnValue('en_US');
  fetchMock.doMock(() =>
    Promise.resolve(
      JSON.stringify({
        locale: 'en_US',
        defaultDomains: ['jsmessages', 'validators'],
        messages: {},
      })
    )
  );

  await initTranslator.fetch();
  await initTranslator.fetch();
  expect(fetchMock).toHaveBeenCalledTimes(1);
});
