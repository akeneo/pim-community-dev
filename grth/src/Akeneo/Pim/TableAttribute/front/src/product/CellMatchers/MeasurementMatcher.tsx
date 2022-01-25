import {CellMatcher} from './index';
import {useAttributeContext} from '../../contexts';
import {MeasurementColumnDefinition} from '../../models';
import {useMeasurementFamilies} from '../../attribute/useMeasurementFamilies';
import {MeasurementValue} from '../../models/MeasurementFamily';

const useSearch: CellMatcher = () => {
  const {attribute} = useAttributeContext();
  const measurementFamilies = useMeasurementFamilies();

  return (cell, searchText, columnCode) => {
    const isSearching = searchText !== '';
    if (!attribute || !isSearching || typeof cell === 'undefined') {
      return false;
    }
    const measurementValue = cell as MeasurementValue;
    if (!measurementValue?.amount || !measurementValue?.unit) return false;

    const column = attribute.table_configuration.find(({code}) => code === columnCode) as MeasurementColumnDefinition;
    const measurementFamilyCode = column.measurementFamilyCode;

    const units = measurementFamilies?.find(({code}) => code === measurementFamilyCode)?.units;
    const unit = units?.find(({code}) => code === measurementValue.unit);
    const valueWithSymbol = `${measurementValue.amount} ${unit?.symbol}`;

    return valueWithSymbol.toLowerCase().includes(searchText.toLowerCase());
  };
};

export default useSearch;
