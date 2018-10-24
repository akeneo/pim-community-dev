import * as React from 'react';
import Value from 'akeneoreferenceentity/domain/model/record/value';
import __ from 'akeneoreferenceentity/tools/translator';
import RecordData from 'akeneoreferenceentity/domain/model/record/data/record';
import RecordSelector from 'akeneoreferenceentity/application/component/app/record-selector';
import {RecordAttribute} from 'akeneoreferenceentity/domain/model/attribute/type/record';

const View = ({value, onChange}: {value: Value; onChange: (value: Value) => void}) => {
  if (!(value.data instanceof RecordData)) {
    return null;
  }

  const attribute = value.attribute as RecordAttribute;

  return (
    <RecordSelector referenceEntityIdentifier={attribute.recordType}/>
  );
};

export const view = View;
