import * as React from 'react';
import Image from 'akeneoreferenceentity/application/component/app/image';
import Value from 'akeneoreferenceentity/domain/model/record/value';
import FileData, {create} from 'akeneoreferenceentity/domain/model/record/data/file';
import __ from 'akeneoreferenceentity/tools/translator';
import File from 'akeneoreferenceentity/domain/model/file';

const View = ({value, onChange}: {value: Value; onChange: (value: Value) => void}) => {
  if (!(value.data instanceof FileData)) {
    return null;
  }

  return (
    <Image
      id={value.attribute.identifier.stringValue()}
      alt={__('pim_reference_entity.record.value.file', {
        '{{ attribute_code }}': value.attribute.getLabel(value.locale.stringValue()),
      })}
      image={value.data.getFile()}
      wide={true}
      onImageChange={(image: File) => {
        const newData = create(image);
        const newValue = value.setData(newData);

        onChange(newValue);
      }}
    />
  );
};

export const view = View;
