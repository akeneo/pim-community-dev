import React from 'react';
import {isTextTarget} from './model';
import {AttributeConfiguratorProps} from '../../../models/Configurator';
import {InvalidAttributeTargetError} from "../error/InvalidAttributeTargetError";

const TextConfigurator = ({target}: AttributeConfiguratorProps) => {
  if (!isTextTarget(target)) {
    throw new InvalidAttributeTargetError(`Invalid target data "${target.code}" for text configurator`);
  }

  return (<></>);
};

export {TextConfigurator};
