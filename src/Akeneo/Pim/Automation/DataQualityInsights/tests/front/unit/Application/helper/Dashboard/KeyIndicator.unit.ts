import {
  roughCount,
  computeTipMessage,
} from '@akeneo-pim-community/data-quality-insights/src/application/helper/Dashboard/KeyIndicator';
import {keyIndicatorsTips} from '@akeneo-pim-community/data-quality-insights/src/application/helper/Dashboard/KeyIndicatorsTips';

test('Compute products number to work on', () => {
  expect(roughCount(1)).toEqual(1);
  expect(roughCount(120)).toEqual(120);

  expect(roughCount(200)).toEqual(200);
  expect(roughCount(201)).toEqual(200);
  expect(roughCount(856)).toEqual(800);

  expect(roughCount(10000)).toEqual(10000);
  expect(roughCount(10001)).toEqual(10000);
  expect(roughCount(99999)).toEqual(99000);

  expect(roughCount(100000)).toEqual(100000);
  expect(roughCount(100001)).toEqual(100000);
  expect(roughCount(990012)).toEqual(990000);

  expect(roughCount(1000000)).toEqual(1000000);
  expect(roughCount(1000001)).toEqual(1000000);
  expect(roughCount(2154110)).toEqual(2000000);
  expect(roughCount(3651484)).toEqual(3000000);
});

test('Compute tip message', () => {
  expect(computeTipMessage(keyIndicatorsTips['has_image'], 1).message).toMatch(/first_step/);
  expect(computeTipMessage(keyIndicatorsTips['has_image'], 59).message).toMatch(/first_step/);
  expect(computeTipMessage(keyIndicatorsTips['has_image'], 59.99).message).toMatch(/first_step/);

  expect(computeTipMessage(keyIndicatorsTips['has_image'], 60).message).toMatch(/second_step/);
  expect(computeTipMessage(keyIndicatorsTips['has_image'], 79).message).toMatch(/second_step/);
  expect(computeTipMessage(keyIndicatorsTips['has_image'], 79.99).message).toMatch(/second_step/);

  expect(computeTipMessage(keyIndicatorsTips['has_image'], 80).message).toMatch(/third_step/);
  expect(computeTipMessage(keyIndicatorsTips['has_image'], 99).message).toMatch(/third_step/);
  expect(computeTipMessage(keyIndicatorsTips['has_image'], 99.56).message).toMatch(/third_step/);

  expect(computeTipMessage(keyIndicatorsTips['has_image'], 100).message).toMatch(/perfect_score_step/);
});
