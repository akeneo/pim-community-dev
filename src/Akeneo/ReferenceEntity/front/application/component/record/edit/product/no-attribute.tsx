import * as React from 'react';
import __ from 'akeneoreferenceentity/tools/translator';
const router = require('pim/router');

const NoAttribute = ({
  onRedirectAttributeCreation,
  referenceEntityLabel,
}: {
  onRedirectAttributeCreation: () => void;
  referenceEntityLabel: string;
}) => {
  const createAttributePath = `#${router.generate(`pim_enrich_attribute_create`)}`;

  return (
    <div className="AknGridContainer-noData">
      <div className="AknGridContainer-noDataImage AknGridContainer-noDataImage--reference-entity" />
      <div className="AknGridContainer-noDataTitle">
        {__('pim_reference_entity.record.product.no_attribute.title', {
          entityLabel: referenceEntityLabel,
        })}
      </div>
      <div className="AknGridContainer-noDataSubtitle">
        {__('pim_reference_entity.record.product.no_attribute.subtitle')}
        <a
          href={createAttributePath}
          title={__('pim_reference_entity.record.product.no_attribute.link')}
          onClick={event => {
            event.preventDefault();

            onRedirectAttributeCreation();

            return false;
          }}
        >
          {__('pim_reference_entity.record.product.no_attribute.link')}
        </a>
      </div>
    </div>
  );
};

export default NoAttribute;
