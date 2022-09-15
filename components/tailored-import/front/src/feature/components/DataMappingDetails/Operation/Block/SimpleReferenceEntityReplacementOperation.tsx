import React from 'react';
import {isReplacementValues, ReplacementValues} from "../../../../models";
import {uuid} from "akeneo-design-system";
import {OperationBlockProps} from "./OperationBlockProps";

const SIMPLE_REFERENCE_ENTITY_REPLACEMENT = 'reference_entity_single_link_replacement';

type SimpleReferenceEntityReplacementOperation = {
    uuid: string;
    type: typeof SIMPLE_REFERENCE_ENTITY_REPLACEMENT;
    mapping: ReplacementValues;
};

const isSimpleReferenceEntityReplacement = (operation?: any): operation is SimpleReferenceEntityReplacementOperation =>
    undefined !== operation &&
    'type' in operation &&
    SIMPLE_REFERENCE_ENTITY_REPLACEMENT === operation.type &&
    'mapping' in operation &&
    isReplacementValues(operation.mapping);

const getDefaultSimpleReferenceEntityReplacementOperation = (): SimpleReferenceEntityReplacementOperation => ({
    uuid: uuid(),
    type: SIMPLE_REFERENCE_ENTITY_REPLACEMENT,
    mapping: {},
});

const SimpleReferenceEntityReplacementOperationBlock = ({
    operation,
    previewData,
    isLastOperation,
    onChange,
    onRemove,
}: OperationBlockProps) => {
    if (!isSimpleReferenceEntityReplacement(operation)) {
        throw new Error('RemoveWhitespaceOperationBlock can only be used with RemoveWhitespaceOperation');
    }

    return (
        <div>pouet</div>
    );
}

export {
    SimpleReferenceEntityReplacementOperationBlock,
    getDefaultSimpleReferenceEntityReplacementOperation,
    SIMPLE_REFERENCE_ENTITY_REPLACEMENT,
};
export type {SimpleReferenceEntityReplacementOperation};
