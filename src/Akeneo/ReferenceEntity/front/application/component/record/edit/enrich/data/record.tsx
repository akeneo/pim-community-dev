import * as React from 'react';
import Value from 'akeneoreferenceentity/domain/model/record/value';
import RecordData, {create} from 'akeneoreferenceentity/domain/model/record/data/record';
import RecordSelector from 'akeneoreferenceentity/application/component/app/record-selector';
import {RecordAttribute} from 'akeneoreferenceentity/domain/model/attribute/type/record';
import RecordCode from 'akeneoreferenceentity/domain/model/record/code';
import __ from 'akeneoreferenceentity/tools/translator';
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
  if (!(value.data instanceof RecordData)) {
    return null;
  }

  const attribute = value.attribute as RecordAttribute;

  return (
    <div className="record-selector-container">
      <RecordSelector
        id={`pim_reference_entity.record.enrich.${value.attribute.getCode().stringValue()}`}
        value={value.data.recordData}
        locale={locale}
        channel={channel}
        placeholder={__('pim_reference_entity.record.selector.no_value')}
        referenceEntityIdentifier={attribute.recordType.getReferenceEntityIdentifier()}
        onChange={(recordCode: RecordCode) => {
          const newData = create(recordCode);
          const newValue = value.setData(newData);

          onChange(newValue);
        }}
      />
    </div>
  );
};

export const view = View;
