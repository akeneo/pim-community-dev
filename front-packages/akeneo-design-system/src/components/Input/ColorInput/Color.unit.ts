import {convertColorToLongHexColor, isValidColor} from './Color';

test('it returns if the hex color is valid', () => {
  expect(isValidColor('#f0f')).toBe(true);
  expect(isValidColor('#F0F')).toBe(true);
  expect(isValidColor('#ffffff')).toBe(true);
  expect(isValidColor('#FFFF0F')).toBe(true);
  expect(isValidColor('#FFFFFG')).toBe(false);
  expect(isValidColor('#FFG')).toBe(false);
  expect(isValidColor('#FFFF')).toBe(false);
  expect(isValidColor('#FFFFFFF')).toBe(false);
});

test('it convert color to long hex color', () => {
  expect(convertColorToLongHexColor('#ff00ff')).toBe('#ff00ff');
  expect(convertColorToLongHexColor('#f0f')).toBe('#ff00ff');
});
