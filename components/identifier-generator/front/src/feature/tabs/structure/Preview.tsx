import React from 'react';
import {Delimiter, PROPERTY_NAMES, Structure, TextTransformation} from '../../models';
import {Preview as PreviewComponent} from 'akeneo-design-system';
import {AutoNumberPreview, DelimiterPreview, FreeTextPreview} from './preview/index';

type PreviewProps = {
  structure: Structure;
  delimiter: Delimiter | null;
  textTransformation: TextTransformation;
};

const Preview: React.FC<PreviewProps> = ({structure, delimiter, textTransformation}) => {
  return (
    <PreviewComponent title={'Preview'}>
      {structure.map((property, i) => (
        <React.Fragment key={JSON.stringify(property)}>
          {i > 0 && delimiter && <DelimiterPreview delimiter={delimiter} textTransformation={textTransformation} />}
          {property.type === PROPERTY_NAMES.FREE_TEXT && (
            <FreeTextPreview property={property} textTransformation={textTransformation} />
          )}
          {property.type === PROPERTY_NAMES.AUTO_NUMBER && <AutoNumberPreview property={property} />}
        </React.Fragment>
      ))}
    </PreviewComponent>
  );
};

export {Preview};
