import React from 'react';
import {Badge} from 'akeneo-design-system';
import {Translate, useTranslate} from '@akeneo-pim-community/shared';
import {ProductType, PRODUCT_TYPE} from 'akeneoassetmanager/domain/model/product/product';
import Completeness from 'akeneoassetmanager/domain/model/product/completeness';

const getLabel = (translate: Translate, completeness: Completeness, type: ProductType) => {
  switch (type) {
    case PRODUCT_TYPE:
      return null === completeness.getRatio() ? translate('pim_common.not_available') : `${completeness.getRatio()} %`;
    default:
      return `${completeness.getCompleteChildren()}/${completeness.getTotalChildren()}`;
  }
};

const getLevel = (completeness: Completeness) => {
  if (completeness.isComplete()) {
    return 'primary';
  } else if (completeness.hasCompleteItems()) {
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
