import {Level} from 'akeneo-design-system';

const getQualityBadgeLevel = (label: string): Level => {
  if (label === 'good') {
    return 'primary';
  }

  if (label === 'to_improve') {
    return 'danger';
  }

  if (label === 'in_progress') {
    return 'warning';
  }

  return 'tertiary';
};

export {getQualityBadgeLevel};
