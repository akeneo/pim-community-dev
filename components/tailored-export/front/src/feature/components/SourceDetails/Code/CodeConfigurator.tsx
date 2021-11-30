import React from 'react';
import {PropertyConfiguratorProps} from '../../../models';
import {isCodeSource} from './model';
import {InvalidPropertySourceError} from '../error';
import {NoOperationsPlaceholder} from '../NoOperationsPlaceholder';

const CodeConfigurator = ({source}: PropertyConfiguratorProps) => {
  if (!isCodeSource(source)) {
    throw new InvalidPropertySourceError(`Invalid source data "${source.code}" for code configurator`);
  }

  return <NoOperationsPlaceholder />;
};

export {CodeConfigurator};
