import React from 'react';
import {AttributeConfiguratorProps} from '../../../models';
import {InvalidAttributeSourceError} from '../error';
import {NoOperationsPlaceholder} from '../NoOperationsPlaceholder';
import {isIdentifierSource} from './model';

const IdentifierConfigurator = ({source}: AttributeConfiguratorProps) => {
  if (!isIdentifierSource(source)) {
    throw new InvalidAttributeSourceError(`Invalid source data "${source.code}" for identifier configurator`);
  }

  return <NoOperationsPlaceholder />;
};

export {IdentifierConfigurator};
