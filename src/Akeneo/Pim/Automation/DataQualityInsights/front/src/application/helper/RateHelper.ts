import {MAX_RATE, Rate} from '../../domain';

const isSuccess = (rate: Rate) => {
  return rate && rate.value === MAX_RATE;
};

export {isSuccess};
