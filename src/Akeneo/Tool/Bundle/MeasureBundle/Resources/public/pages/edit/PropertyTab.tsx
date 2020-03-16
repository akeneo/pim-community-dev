import React from 'react';
import {MeasurementFamily} from 'akeneomeasure/model/measurement-family';

const PropertyTab = ({
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
      Properties for {measurementFamily.code}
    </div>
  );
};

export {PropertyTab};
