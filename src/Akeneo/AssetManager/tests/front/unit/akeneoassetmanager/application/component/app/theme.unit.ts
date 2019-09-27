import {opacity} from 'akeneoassetmanager/application/component/app/theme';

test('It can compute a transparency color from hex code', () => {
  expect(opacity('#ffffff', 0.5)).toEqual('#ffffff80');
  expect(opacity('#ffffff', 0)).toEqual('#ffffff00');
  expect(opacity('#ffffff', 1)).toEqual('#ffffffff');
});
