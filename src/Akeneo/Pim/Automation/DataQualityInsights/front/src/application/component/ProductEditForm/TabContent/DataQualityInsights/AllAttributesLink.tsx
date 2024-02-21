import React, {FunctionComponent} from 'react';
import {
  DATA_QUALITY_INSIGHTS_FILTER_ALL_MISSING_ATTRIBUTES,
  DATA_QUALITY_INSIGHTS_FILTER_ALL_IMPROVABLE_ATTRIBUTES,
} from '../../../../listener';

const __ = require('oro/translator');

interface AllAttributesLinkProps {
  axis: string;
  attributes: string[];
}

const handleClick = (attributes: string[], axis: string) => {
  switch (axis) {
    case 'enrichment':
      window.dispatchEvent(
        new CustomEvent(DATA_QUALITY_INSIGHTS_FILTER_ALL_MISSING_ATTRIBUTES, {
          detail: {
            attributes: attributes,
          },
        })
      );
      break;
    case 'consistency':
      window.dispatchEvent(
        new CustomEvent(DATA_QUALITY_INSIGHTS_FILTER_ALL_IMPROVABLE_ATTRIBUTES, {
          detail: {
            attributes: attributes,
          },
        })
      );
      break;
  }
};

const AllAttributesLink: FunctionComponent<AllAttributesLinkProps> = ({axis, attributes}) => {
  return (
    <span
      onClick={() => handleClick(attributes, axis)}
      className="AknSubsection-comment AknSubsection-comment--clickable"
    >
      {__(`akeneo_data_quality_insights.product_evaluation.axis.${axis}.attributes_link`)}
    </span>
  );
};

export default AllAttributesLink;
