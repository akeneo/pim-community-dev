import React from 'react';
import {CellInput} from './index';
import {TableInput} from 'akeneo-design-system';
import {getLabel, useTranslate} from '@akeneo-pim-community/shared';
import {MeasurementColumnDefinition} from '../../models';
import {useLocaleCode} from '../../contexts';
import {MeasurementValue} from '../../models/MeasurementFamily';
import {useMeasurementUnits} from '../../attribute/useMeasurementUnits';

const MeasurementInput: CellInput = ({columnDefinition, highlighted, inError, row, onChange, ...rest}) => {
  const translate = useTranslate();
  const localeCode = useLocaleCode();

  const column = columnDefinition as MeasurementColumnDefinition;
  const cell = row[column.code] as MeasurementValue | undefined;
  const units = useMeasurementUnits(column.measurement_family_code);

  const unitsTranslated = React.useMemo(
    () =>
      units?.map(({code, labels, symbol}) => ({
        value: code,
        label: getLabel(labels, localeCode, code),
        symbol,
      })) || [],
    [localeCode, units]
  );

  const handleChange = React.useCallback(
    (amount: string | undefined, unit: string) => {
      if (typeof amount === 'undefined' || '' === amount) {
        onChange(undefined);
      } else {
        onChange({amount, unit});
      }
    },
    [onChange]
  );

  return (
    <TableInput.Measurement
      amount={cell?.amount || ''}
      unit={cell?.unit || column.measurement_default_unit_code}
      onChange={handleChange}
      units={unitsTranslated}
      emptyResultLabel={translate('pim_common.no_result')}
      openLabel={translate('pim_common.open')}
      highlighted={highlighted}
      inError={inError}
      {...rest}
    />
  );
};

export default MeasurementInput;
