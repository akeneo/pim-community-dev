import {
  ConcreteMediaLinkAttribute,
  isMediaLinkAttribute,
} from 'akeneoassetmanager/domain/model/attribute/type/media-link';

const normalizedMediaLink = {
  identifier: 'url',
  asset_family_identifier: 'designer',
  code: 'url',
  labels: {en_US: 'Url'},
  type: 'media_link',
  order: 0,
  value_per_locale: true,
  value_per_channel: false,
  is_required: true,
  is_read_only: true,
  suffix: null,
  prefix: 'http://google.com/',
  media_type: 'image',
};

describe('akeneo > attribute > domain > model > attribute > type --- MediaLinkAttribute', () => {
  test('I can create a ConcreteMediaLinkAttribute from normalized', () => {
    expect(ConcreteMediaLinkAttribute.createFromNormalized(normalizedMediaLink).normalize()).toEqual(
      normalizedMediaLink
    );
  });

  test('I cannot create an invalid ConcreteMediaLinkAttribute', () => {
    expect(() => {
      new ConcreteMediaLinkAttribute('url', 'designer', 'url', {en_US: 'Url'}, true, false, 0, true, false);
    }).toThrow('Attribute expects a valid Prefix as prefix');
    expect(() => {
      new ConcreteMediaLinkAttribute('url', 'designer', 'url', {en_US: 'Url'}, true, false, 0, true, false, null);
    }).toThrow('Attribute expects a valid Suffix as suffix');
    expect(() => {
      new ConcreteMediaLinkAttribute(
        'url',
        'designer',
        'url',
        {en_US: 'Url'},
        true,
        false,
        0,
        true,
        false,
        null,
        'google.com'
      );
    }).toThrow('Attribute expects a valid MediaType as mediaType');
  });

  test('I can check if it is a media-link attribute', () => {
    expect(isMediaLinkAttribute(normalizedMediaLink)).toBe(true);
    expect(isMediaLinkAttribute({...normalizedMediaLink, type: 'noice'})).toBe(false);
  });
});
