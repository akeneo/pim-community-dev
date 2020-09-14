import { MeasurementUnitCode } from './MeasurementFamily';

type MeasurementData = {
  unit: MeasurementUnitCode | null;
  amount: number | null;
};

const isMeasurementAmountFilled = (value: any): boolean => {
  return (
    value &&
    Object.prototype.hasOwnProperty.call(value, 'amount') &&
    value.amount !== '' &&
    value.amount !== null
  );
};

const isMeasurementUnitFilled = (value: any): boolean => {
  return (
    value && Object.prototype.hasOwnProperty.call(value, 'unit') && !!value.unit
  );
};

const parseMeasurementValue = (value: any): MeasurementData => {
  if (
    value &&
    Object.prototype.hasOwnProperty.call(value, 'amount') &&
    Object.prototype.hasOwnProperty.call(value, 'unit')
  ) {
    return { unit: value.unit, amount: value.amount };
  }

  return { unit: '', amount: null };
};

export {
  MeasurementData,
  isMeasurementUnitFilled,
  isMeasurementAmountFilled,
  parseMeasurementValue,
};
