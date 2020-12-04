import {Level} from 'akeneo-design-system';
import {Tip, KeyIndicatorTips} from '../../../domain';

const getProgressBarLevel = (ratio: number): Level => {
  if (ratio >= 50) {
    return 'primary';
  }
  if (ratio >= 25) {
    return 'warning';
  }
  if (ratio >= 1) {
    return 'danger';
  }

  return 'tertiary';
};

const computeProductsNumberToWorkOn = (productsNumber: number): number => {
  if (productsNumber < 200) {
    return productsNumber;
  }
  let roundValue = 100;
  if (productsNumber >= 10001 && productsNumber < 100000) {
    roundValue = 1000;
  } else if (productsNumber >= 100001 && productsNumber < 1000000) {
    roundValue = 10000;
  } else if (productsNumber >= 1000001) {
    roundValue = 1000000;
  }

  return Math.floor(productsNumber / roundValue) * roundValue;
};

const computeTipMessage = (tips: KeyIndicatorTips, ratio: number): Tip => {
  let stepKey: string = 'perfect_score_step';
  if (ratio < 60) {
    stepKey = 'first_step';
  } else if (ratio < 80) {
    stepKey = 'second_step';
  } else if (ratio < 100) {
    stepKey = 'third_step';
  }

  const messageId = generateRandomNumber(tips[stepKey].length - 1);

  return tips[stepKey][messageId];
};

const generateRandomNumber = (max: number): number => {
  return Math.round(Math.random() * max);
};

export {computeTipMessage, computeProductsNumberToWorkOn, getProgressBarLevel};
