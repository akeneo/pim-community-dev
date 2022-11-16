import React from 'react';
import {Property, PROPERTY_NAMES} from '../../models';
import {FreeTextLine} from './';

type PropertyLineProps = {
  property: Property;
};

const PropertyLine: React.FC<PropertyLineProps> = ({property}) => {
  return <>{property.type === PROPERTY_NAMES.FREE_TEXT && <FreeTextLine freeTextProperty={property} />}</>;
};

export {PropertyLine};
