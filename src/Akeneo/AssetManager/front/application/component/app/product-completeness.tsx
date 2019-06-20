import * as React from 'react';
import __ from 'akeneoreferenceentity/tools/translator';
import {ProductType, PRODUCT_TYPE} from 'akeneoreferenceentity/domain/model/product/product';
import Completeness from 'akeneoreferenceentity/domain/model/product/completeness';

const memo = (React as any).memo;

const getLabel = (completeness: Completeness, type: ProductType) => {
  return PRODUCT_TYPE === type
    ? `${completeness.getRatio()} %`
    : `${completeness.getCompleteChildren()}/${completeness.getTotalChildren()}`;
};

const getCompletenessClass = (completeness: Completeness) => {
  if (completeness.isComplete()) {
    return `AknBadge AknBadge--success`;
  } else if (completeness.hasCompleteItems()) {
    return `AknBadge AknBadge--warning`;
  } else {
    return `AknBadge AknBadge--important`;
  }
};

const ProductCompletenessLabel = memo(({completeness, type}: {completeness: Completeness; type: ProductType}) => {
  return (
    <div className="AknGrid-bodyCell string-cell AknBadge--topRight">
      <span className={getCompletenessClass(completeness)}>{getLabel(completeness, type)}</span>
    </div>
  );
});

export default ProductCompletenessLabel;
