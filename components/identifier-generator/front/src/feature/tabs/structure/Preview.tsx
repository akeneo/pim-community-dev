import React from 'react';
import {Delimiter, PROPERTY_NAMES, Structure} from '../../models';
import {Preview as PreviewComponent} from 'akeneo-design-system';
import {AutoNumberPreview, DelimiterPreview, FreeTextPreview} from './preview';

type PreviewProps = {
  structure: Structure;
  delimiter: Delimiter | null;
};

const Preview: React.FC<PreviewProps> = ({structure, delimiter}) => {
  return (
    <PreviewComponent title={'Preview'}>
      {structure.map((property, i) => (
        <React.Fragment key={JSON.stringify(property)}>
          {i > 0 && delimiter && <DelimiterPreview delimiter={delimiter} />}
          {property.type === PROPERTY_NAMES.FREE_TEXT && <FreeTextPreview property={property} />}
          {property.type === PROPERTY_NAMES.AUTO_NUMBER && <AutoNumberPreview property={property} />}
        </React.Fragment>
      ))}
    </PreviewComponent>
  );
};

export {Preview};
