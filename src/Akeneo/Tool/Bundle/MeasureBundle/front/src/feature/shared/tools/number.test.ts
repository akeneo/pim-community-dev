import {unformatNumber, formatNumber} from './number';

describe('tools/number.ts', () => {
  it('should unformat a number with the provided decimal separator', () => {
    expect(unformatNumber(',')('3,14')).toEqual('3.14');
    expect(unformatNumber('')('3.14')).toEqual('3.14');
  });

  it('should format a number with the provided decimal separator', () => {
    expect(formatNumber(',')('3.14')).toEqual('3,14');
    expect(formatNumber('')('3.14')).toEqual('3.14');
  });
});
