import {translate} from './translate';
// @ts-ignore
import Translator from '../../legacy/translator';

afterEach(() => jest.restoreAllMocks());

test('it translates with the symfony placeholder syntax', () => {
  Translator.fromJSON(
    JSON.stringify({
      locale: 'en_US',
      defaultDomains: ['jsmessages'],
      messages: {'jsmessages:pim.hello': 'Hello {{ name }}'},
    })
  );

  expect(translate('pim.hello', {name: 'Julia'})).toBe('Hello Julia');
  expect(translate('pim.unknown')).toBe('pim.unknown');
});

test('it reports missing translations in debug mode', () => {
  const error = jest.spyOn(console, 'error').mockImplementation(() => {});
  Translator.fromJSON({debug: true, locale: 'en_US', defaultDomains: 'jsmessages', messages: {}});
  Translator.add('jsmessages:pim.known', 'Known');
  Translator.add('pim.root', 'Root');

  expect(translate('pim.known')).toBe('Known');
  expect(translate('pim.root')).toBe('Root');
  expect(error).not.toHaveBeenCalled();

  translate('pim.missing');
  expect(error).toHaveBeenCalledWith('Untranslated: %s', 'pim.missing');

  Translator.fromJSON({locale: 'en_US', messages: {}});
});

test('it handles a list of default domains in debug mode', () => {
  const error = jest.spyOn(console, 'error').mockImplementation(() => {});
  Translator.fromJSON({debug: true, locale: 'en_US', defaultDomains: ['jsmessages', 'validators'], messages: {}});
  Translator.add('validators:pim.valid', 'Valid');

  expect(translate('pim.valid')).toBe('Valid');
  expect(error).not.toHaveBeenCalled();

  Translator.fromJSON({locale: 'en_US', messages: {}});
});
