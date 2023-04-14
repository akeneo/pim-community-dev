import React from 'react';
import {Delimiter, PROPERTY_NAMES, Structure, TextTransformation} from '../../models';
// Do not fix this import, it will break build for others otherwise
import {
  AutoNumberPreview,
  DelimiterPreview,
  FamilyCodePreview,
  FreeTextPreview,
  AttributePreview,
} from './preview/index';
import {Styled} from '../../components/Styled';

type PreviewProps = {
  structure: Structure;
  delimiter: Delimiter | null;
  textTransformation: TextTransformation;
};

const Preview: React.FC<PreviewProps> = ({structure, delimiter, textTransformation}) => {
  return (
    <Styled.PreviewWithTextTransformation title={'Preview'} textTransformation={textTransformation}>
      {structure.map((property, i) => (
        <React.Fragment key={JSON.stringify(property)}>
          {i > 0 && delimiter && <DelimiterPreview delimiter={delimiter} />}
          {property.type === PROPERTY_NAMES.FREE_TEXT && <FreeTextPreview property={property} />}
          {property.type === PROPERTY_NAMES.AUTO_NUMBER && <AutoNumberPreview property={property} />}
          {property.type === PROPERTY_NAMES.FAMILY && <FamilyCodePreview property={property} />}
          {property.type === PROPERTY_NAMES.SIMPLE_SELECT && <AttributePreview property={property} />}
          {property.type === PROPERTY_NAMES.REF_ENTITY && <AttributePreview property={property} />}
        </React.Fragment>
      ))}
    </Styled.PreviewWithTextTransformation>
  );
};

export {Preview};
