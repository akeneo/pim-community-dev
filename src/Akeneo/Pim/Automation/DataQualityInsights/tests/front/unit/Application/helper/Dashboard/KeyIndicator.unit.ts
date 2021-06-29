import {
  computeProductsNumberToWorkOn,
  computeTipMessage,
} from '@akeneo-pim-community/data-quality-insights/src/application/helper/Dashboard/KeyIndicator';
import {keyIndicatorsTips} from '@akeneo-pim-community/data-quality-insights/src/application/helper/Dashboard/KeyIndicatorsTips';

test('Compute products number to work on', () => {
  expect(computeProductsNumberToWorkOn(1)).toEqual(1);
  expect(computeProductsNumberToWorkOn(120)).toEqual(120);

  expect(computeProductsNumberToWorkOn(200)).toEqual(200);
  expect(computeProductsNumberToWorkOn(201)).toEqual(200);
  expect(computeProductsNumberToWorkOn(856)).toEqual(800);

  expect(computeProductsNumberToWorkOn(10000)).toEqual(10000);
  expect(computeProductsNumberToWorkOn(10001)).toEqual(10000);
  expect(computeProductsNumberToWorkOn(99999)).toEqual(99000);

  expect(computeProductsNumberToWorkOn(100000)).toEqual(100000);
  expect(computeProductsNumberToWorkOn(100001)).toEqual(100000);
  expect(computeProductsNumberToWorkOn(990012)).toEqual(990000);

  expect(computeProductsNumberToWorkOn(1000000)).toEqual(1000000);
  expect(computeProductsNumberToWorkOn(1000001)).toEqual(1000000);
  expect(computeProductsNumberToWorkOn(2154110)).toEqual(2000000);
  expect(computeProductsNumberToWorkOn(3651484)).toEqual(3000000);
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
