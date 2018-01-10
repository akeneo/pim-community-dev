import i18n from 'pim/i18n';

test('get existing label provide it', () => {
  expect(i18n.getLabel({en_US: 'My label'}, 'en_US', 'my_code')).toBe('My label');
});
