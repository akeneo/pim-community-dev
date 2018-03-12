import {Boolean} from 'pimfront/product-grid/domain/model/filter/value';

export default (value: any) => {
  if (typeof value === 'boolean') {
    return Boolean.fromValue(value);
  }

  return null;
};
