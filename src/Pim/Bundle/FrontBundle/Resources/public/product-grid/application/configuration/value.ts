import {Boolean, Null} from 'pimfront/product-grid/domain/model/filter/value';

export default (value: any) => {
  if (typeof value === 'boolean') {
    return Boolean.fromValue(value);
  }

  if (value === null) {
    return Null.fromValue(value);
  }

  return null;
};
