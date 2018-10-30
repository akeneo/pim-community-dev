import * as React from 'react';
import Value from 'akeneoreferenceentity/domain/model/record/value';
import RecordSelector from 'akeneoreferenceentity/application/component/app/record-selector';
import {RecordAttribute} from 'akeneoreferenceentity/domain/model/attribute/type/record';
import RecordCode from 'akeneoreferenceentity/domain/model/record/code';
import __ from 'akeneoreferenceentity/tools/translator';
import RecordCollectionData from 'akeneoreferenceentity/domain/model/record/data/record-collection';
import ChannelReference from 'akeneoreferenceentity/domain/model/channel-reference';
import LocaleReference from 'akeneoreferenceentity/domain/model/locale-reference';

const View = ({
  value,
  onChange,
  channel,
  locale,
}: {
  value: Value;
  channel: ChannelReference;
  locale: LocaleReference;
  onChange: (value: Value) => void;
}) => {
  if (!(value.data instanceof RecordCollectionData)) {
    return null;
  }

  const attribute = value.attribute as RecordAttribute;

  return (
    //The first children of a FieldContainer will stretch to the full width if not contained in a div.
    //I didn't found a better way to fix it. So we need this class
    <div className="record-selector-container">
      <RecordSelector
        value={value.data.recordData}
        multiple={true}
        locale={locale}
        channel={channel}
        placeholder={__('pim_reference_entity.record.selector.no_value')}
        referenceEntityIdentifier={attribute.recordType.getReferenceEntityIdentifier()}
        onChange={(recordCodes: RecordCode[]) => {
          const newData = RecordCollectionData.create(recordCodes);
          const newValue = value.setData(newData);

          onChange(newValue);
        }}
      />
    </div>
  );
};

export const view = View;
