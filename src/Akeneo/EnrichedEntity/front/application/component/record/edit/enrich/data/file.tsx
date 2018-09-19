import * as React from 'react';
import Image from 'akeneoenrichedentity/application/component/app/image';
import Value from 'akeneoenrichedentity/domain/model/record/value';
import FileData, {create} from 'akeneoenrichedentity/domain/model/record/data/file';
import __ from 'akeneoenrichedentity/tools/translator';
import File from 'akeneoenrichedentity/domain/model/file';

const View = ({value, onChange}: {value: Value; onChange: (value: Value) => void}) => {
  if (!(value.data instanceof FileData)) {
    return null;
  }

  return (
    <Image
      alt={__('pim_enriched_entity.record.value.file', {
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
