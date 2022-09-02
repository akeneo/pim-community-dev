import {FilterViewCollection, getFilterViews} from '../configuration/value';
import {useValueConfig} from './useValueConfig';
import {NormalizedAttribute} from '../../domain/model/attribute/attribute';

const useFilterViewsGenerator = () => {
  const valueConfig = useValueConfig();

  return (attributes: NormalizedAttribute[]): FilterViewCollection => getFilterViews(valueConfig, attributes);
};

export {useFilterViewsGenerator};
