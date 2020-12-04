import Rates from './Rates.interface';

export default interface Axis {
  code: string;
  rates: Rates;
}

export interface AxesCollection {
  [axis: string]: Axis;
}
