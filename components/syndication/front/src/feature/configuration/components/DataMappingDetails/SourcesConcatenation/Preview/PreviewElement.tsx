import React from 'react';
import {SkeletonPlaceholder} from 'akeneo-design-system';
import {getLabel, useTranslate, useUserContext} from '@akeneo-pim-community/shared';
import {Source} from '../../../../models';
import {useAssociationType, useAttribute} from '../../../../hooks';
import {isStaticStringSource} from '../../../SourceDetails/Static';

type SourceElementProps = {
  source: Source;
};

const AttributeSourceElement = ({source}: SourceElementProps) => {
  const [isFetching, attribute] = useAttribute(source.code);
  const catalogLocale = useUserContext().get('catalogLocale');

  if (isFetching) {
    return <SkeletonPlaceholder as="span">{source.code}</SkeletonPlaceholder>;
  }

  return <>{getLabel(attribute?.labels ?? {}, catalogLocale, source.code)}</>;
};

const AssociationTypeSourceElement = ({source}: SourceElementProps) => {
  const [isFetching, associationType] = useAssociationType(source.code);
  const catalogLocale = useUserContext().get('catalogLocale');

  if (isFetching) {
    return <SkeletonPlaceholder as="span">{source.code}</SkeletonPlaceholder>;
  }

  return <>{getLabel(associationType?.labels ?? {}, catalogLocale, source.code)}</>;
};

const PropertySourceElement = ({source}: SourceElementProps) => {
  const translate = useTranslate();

  return <>{translate(`pim_common.${source.code}`)}</>;
};

const StaticSourceElement = ({source}: SourceElementProps) => {
  if (!isStaticStringSource(source)) {
    throw new Error('Invalid source type');
  }

  return <>{source.value}</>;
};

export {AssociationTypeSourceElement, AttributeSourceElement, PropertySourceElement, StaticSourceElement};
