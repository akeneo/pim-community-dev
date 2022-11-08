import React from 'react';
import {Property, PROPERTY_NAMES} from '../../../models';
import {FreeTextPreview} from './FreeTextPreview';

type PropertyPreviewProps = {
  property: Property;
};

const PropertyPreview: React.FC<PropertyPreviewProps> = ({property}) => {
  return <>{property.type === PROPERTY_NAMES.FREE_TEXT && <FreeTextPreview freeTextProperty={property} />}</>;
};

export {PropertyPreview};
