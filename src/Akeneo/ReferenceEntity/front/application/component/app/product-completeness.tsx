import * as React from 'react';
import __ from 'akeneoreferenceentity/tools/translator';
import {ProductType, PRODUCT_TYPE} from 'akeneoreferenceentity/domain/model/product/product';
import Completeness from 'akeneoreferenceentity/domain/model/product/completeness';

const memo = (React as any).memo;

const getLabel = (completeness: Completeness, type: ProductType) => {
  return `${
    PRODUCT_TYPE === type
      ? completeness.getRatio() + ' %'
      : completeness.getCompleteCount() + '/' + completeness.getRequiredCount()
  }`;
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

const getTranslationKey = (completeness: Completeness) => {
  const keyBase = 'pim_reference_entity.record.grid.completeness';

  if (completeness.isComplete()) {
    return `${keyBase}.title_complete`;
  } else if (completeness.hasCompleteItems()) {
    return `${keyBase}.title_ongoing`;
  } else {
    return `${keyBase}.title_non_complete`;
  }
};

const ProductCompletenessLabel = memo(({completeness, type}: {completeness: Completeness; type: ProductType}) => {
  return (
    <div className="AknGrid-bodyCell string-cell AknBadge--topRight">
      <span
        title={__(getTranslationKey(completeness), {
          complete: completeness.getCompleteCount(),
          required: completeness.getRequiredCount(),
        })}
        className={getCompletenessClass(completeness)}
      >
        {getLabel(completeness, type)}
      </span>
    </div>
  );
});

export default ProductCompletenessLabel;
