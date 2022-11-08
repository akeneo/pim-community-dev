import React from 'react';
import {PropertyWithIdentifier} from '../../models';
import {FreeText} from './FreeText';
import {PROPERTY_NAMES} from '../../models';

type PropertyProps = {
  property: PropertyWithIdentifier;
  onClick: (id: string) => void;
};

const Property: React.FC<PropertyProps> = ({property, onClick}) => {
  return (
    <>{property.type === PROPERTY_NAMES.FREE_TEXT && <FreeText freeTextProperty={property} onClick={onClick} />}</>
  );
};

export {Property};
