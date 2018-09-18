import * as React from 'react';
import Value from 'akeneoenrichedentity/domain/model/record/value';
import TextData, {create} from 'akeneoenrichedentity/domain/model/record/data/text';
import Flag from 'akeneoenrichedentity/tools/component/flag';
import {createLocaleFromCode} from 'akeneoenrichedentity/domain/model/locale';

const View = ({value, onChange}: {value: Value, onChange: (value: Value) => void}) => {
  if (!(value.data instanceof TextData)) {
    return null;
  }


  return (
    <React.Fragment>
      <input className="AknTextField AknTextField--withBottomBorder" value={value.data.stringValue()} onChange={(event: React.ChangeEvent<HTMLInputElement>) => {
        const newData = create(event.currentTarget.value);
        const newValue = value.setData(newData);

        onChange(newValue);
      }}/>
      <Flag locale={createLocaleFromCode(value.locale.stringValue())} displayLanguage={false} />
    </React.Fragment>
  );
};

export const view = View;
