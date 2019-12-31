import React, {FunctionComponent} from 'react';

const __ = require('oro/translator');

interface AllAttributesLinkProps {
  axis: string;
  attributes: string[];
}

const handleClick = (attributes: string[]) => {
  alert(`attributes: [${attributes.join(', ')}]`)
};

const AllAttributesLink: FunctionComponent<AllAttributesLinkProps> = ({axis, attributes}) => {
  return (
    <span onClick={() => handleClick(attributes)} className="AknSubsection-comment AknSubsection-comment--clickable">
      {__(`akeneo_data_quality_insights.product_evaluation.axis.${axis}.attributes_link`)}
    </span>
  );
};

export default AllAttributesLink;
