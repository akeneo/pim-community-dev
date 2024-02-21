import {getChannelTranslation} from './channelHelper';
import {aChannelList} from '../../tests/provideChannelHelper';

describe('channelHelper', () => {
  test('it can get channel translation', () => {
    const channelList = aChannelList();
    const locale = 'de_DE';
    const channelCode = 'print';

    const channelTranslated = getChannelTranslation(channelList, channelCode, locale);
    expect(channelTranslated).toEqual('Drucken');
  });

  test('it can get default translation when no locale available', () => {
    const channelList = aChannelList();
    const locale = 'es_ES';
    const channelCode = 'print';

    const channelTranslated = getChannelTranslation(channelList, channelCode, locale);
    expect(channelTranslated).toEqual('[print]');
  });

  test('it does not get channel translation', () => {
    const channelList = aChannelList();
    const locale = 'en_US';
    const channelCode = null;

    const channelTranslated = getChannelTranslation(channelList, channelCode, locale);
    expect(channelTranslated).toEqual(null);
  });
});
