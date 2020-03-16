import React from 'react';
import {MeasurementFamily} from 'akeneomeasure/model/measurement-family';

const UnitTab = ({
  measurementFamily,
  onMeasurementFamilyChange,
}: {
  measurementFamily: MeasurementFamily;
  onMeasurementFamilyChange: (measurementFamily: MeasurementFamily) => void;
}) => {
  return (
    <div
      onClick={() => {
        onMeasurementFamilyChange({...measurementFamily, code: 'nice from unit tab'});
      }}
    >
      Units for {measurementFamily.code}
    </div>
  );
};

export {UnitTab};
