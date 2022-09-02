import React from 'react';
import {Badge} from 'akeneo-design-system';
import {Translate, useTranslate} from '@akeneo-pim-community/shared';
import {Completeness, ProductType, PRODUCT_TYPE} from 'akeneoassetmanager/domain/model/product/product';

const getLabel = (translate: Translate, completeness: Completeness, type: ProductType) => {
  switch (type) {
    case PRODUCT_TYPE:
      return null === completeness.ratio ? translate('pim_common.not_available') : `${completeness.ratio} %`;
    default:
      return `${completeness.completeChildren}/${completeness.totalChildren}`;
  }
};

const getLevel = (completeness: Completeness) => {
  if (
    completeness.completeChildren === 0 && completeness.totalChildren === 0
      ? completeness.ratio === 100
      : completeness.completeChildren === completeness.totalChildren
  ) {
    return 'primary';
  } else if ((null !== completeness.ratio && completeness.ratio > 0) || completeness.completeChildren > 0) {
    return 'warning';
  } else {
    return 'danger';
  }
};

type ProductCompletenessProps = {
  completeness: Completeness;
  type: ProductType;
};

const ProductCompleteness = ({completeness, type}: ProductCompletenessProps) => {
  const translate = useTranslate();

  return <Badge level={getLevel(completeness)}>{getLabel(translate, completeness, type)}</Badge>;
};

export {ProductCompleteness};
