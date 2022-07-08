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

export const roughCount = (count: number): number => {
  if (count < 200) {
    return count;
  }
  let roundValue = 100;
  if (count >= 10001 && count < 100000) {
    roundValue = 1000;
  } else if (count >= 100001 && count < 1000000) {
    roundValue = 10000;
  } else if (count >= 1000001) {
    roundValue = 1000000;
  }

  return Math.floor(count / roundValue) * roundValue;
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

export {computeTipMessage, getProgressBarLevel};
