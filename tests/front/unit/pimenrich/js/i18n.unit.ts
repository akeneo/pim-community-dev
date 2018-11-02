import * as i18n from 'pimui/js/i18n';

describe('>>>TOOLS --- i18n', () => {
  test('get label for existing translation', () => {
    expect(i18n.getLabel({en_US: 'My label'}, 'en_US', 'my_code')).toBe('My label');
  });

  test('fallback to code when translation is not available', () => {
    expect(i18n.getLabel({en_US: 'My label'}, 'fr_FR', 'my_code')).toBe('[my_code]');
  });

  test('Generate an html flag', () => {
    expect(i18n.getFlag('en_US')).toBe(`
<span class=\"flag-language\">
  <i class=\"flag flag-us\"></i>
  <span class=\"language\">en</span>
</span>`);
  });

  test('Generate an html flag with a long locale code', () => {
    expect(i18n.getFlag('en_US_FR')).toBe(`
<span class=\"flag-language\">
  <i class=\"flag flag-fr\"></i>
  <span class=\"language\">en</span>
</span>`);
  });

  test('Generate an html flag without the language', () => {
    expect(i18n.getFlag('en_US', false)).toBe(`
<span class=\"flag-language\">
  <i class=\"flag flag-us\"></i>
</span>`);
  });

  test('Generate nothing if the locale is not specified', () => {
    expect(i18n.getFlag()).toBe('');
  });
});
