import React from 'react';

type AssociationType = {
  code: string;
};

type QuantifiedLink = {
  identifier: string;
  quantity: string;
};

type QuantifiedAssociationCollection = {
  [associationTypeCode: string]: {
    products: QuantifiedLink[];
    product_model: QuantifiedLink[];
  };
};

type QuantifiedAssociationsProps = {
  value: QuantifiedAssociationCollection;
  associationTypes: AssociationType[];
  onAssociationsChange: (updatedValue: QuantifiedAssociationCollection) => void;
  onOpenPicker: () => void;
};

const QuantifiedAssociations = ({
  value,
  associationTypes,
  onAssociationsChange,
  onOpenPicker,
}: QuantifiedAssociationsProps) => {
  return (
    <div>
      <span onClick={onOpenPicker}>add association</span>
    </div>
  );
};

export {QuantifiedAssociations};
