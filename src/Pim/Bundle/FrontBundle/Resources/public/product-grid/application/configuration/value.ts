import {Boolean, Null, String, Collection} from 'pimfront/product-grid/domain/model/filter/value';

export default (value: any) => {
  if (value === null) {
    return Null.fromValue(value);
  }
  if (typeof value === 'boolean') {
    return Boolean.fromValue(value);
  }
  if (typeof value === 'string') {
    return String.fromValue(value);
  }
  if (Object.prototype.toString.call(value) === '[object Array]') {
    return Collection.fromValue(value);
  }

  return null;
};
