import {
  defaultCatalogLocaleChanged,
  catalogLocaleChanged,
  uiLocaleChanged,
  catalogChannelChanged,
} from 'akeneoreferenceentity/domain/event/user';

describe('akeneo > reference entity > domain > event --- user', () => {
  test('I can create a defaultCatalogLocaleChanged event', () => {
    expect(defaultCatalogLocaleChanged('fr_FR')).toEqual({
      type: 'DEFAULT_LOCALE_CHANGED',
      locale: 'fr_FR',
      target: 'defaultCatalog',
    });
  });
  test('I can create a catalogLocaleChanged event', () => {
    expect(catalogLocaleChanged('en_US')).toEqual({type: 'LOCALE_CHANGED', locale: 'en_US', target: 'catalog'});
  });
  test('I can create a uiLocaleChanged event', () => {
    expect(uiLocaleChanged('en_US')).toEqual({type: 'LOCALE_CHANGED', locale: 'en_US', target: 'ui'});
  });
  test('I can create a catalogChannelChanged event', () => {
    expect(catalogChannelChanged('ecommerce')).toEqual({
      type: 'CHANNEL_CHANGED',
      channel: 'ecommerce',
      target: 'catalog',
    });
  });
});
