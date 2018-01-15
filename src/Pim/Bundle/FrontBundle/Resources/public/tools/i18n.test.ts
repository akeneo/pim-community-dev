import i18n from './i18n';
describe('>>>TOOLS --- i18n', () => {
  test('get label for existing translation', () => {
    expect(i18n.getLabel({en_US: 'My label'}, 'en_US', 'my_code')).toBe('My label');
  });

  test('fallback to code when translation is not available', () => {
    expect(i18n.getLabel({en_US: 'My label'}, 'fr_FR', 'my_code')).toBe('[my_code]');
  });
});
