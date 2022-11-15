import React from 'react';
import {PropertyWithIdentifier} from '../../models';
import {FreeTextLine} from './';
import {PROPERTY_NAMES} from '../../models';

type PropertyProps = {
  property: PropertyWithIdentifier;
};

const Property: React.FC<PropertyProps> = ({property}) => {
  return <>{property.type === PROPERTY_NAMES.FREE_TEXT && <FreeTextLine freeTextProperty={property} />}</>;
};

export {Property};
