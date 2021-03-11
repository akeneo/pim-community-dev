import React from 'react';
import {Badge} from 'akeneo-design-system';
import {ProductType, PRODUCT_TYPE} from 'akeneoassetmanager/domain/model/product/product';
import Completeness from 'akeneoassetmanager/domain/model/product/completeness';

const getLabel = (completeness: Completeness, type: ProductType) =>
  PRODUCT_TYPE === type
    ? `${completeness.getRatio()} %`
    : `${completeness.getCompleteChildren()}/${completeness.getTotalChildren()}`;

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

const ProductCompleteness = ({completeness, type}: ProductCompletenessProps) => (
  <Badge level={getLevel(completeness)}>{getLabel(completeness, type)}</Badge>
);

export {ProductCompleteness};
