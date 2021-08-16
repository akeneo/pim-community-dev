import React from 'react';
import {AttributeConfiguratorProps} from '../../../models';
import {InvalidAttributeSourceError} from '../error';
import {NoOperationsPlaceholder} from '../NoOperationsPlaceholder';
import {isTextSource} from './model';

const TextConfigurator = ({source}: AttributeConfiguratorProps) => {
  if (!isTextSource(source)) {
    throw new InvalidAttributeSourceError(`Invalid source data "${source.code}" for text configurator`);
  }

  return <NoOperationsPlaceholder />;
};

export {TextConfigurator};
