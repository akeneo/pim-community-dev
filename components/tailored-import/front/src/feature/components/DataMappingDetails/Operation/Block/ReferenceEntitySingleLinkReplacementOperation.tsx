import React from 'react';
import {isReplacementValues, ReplacementValues} from "../../../../models";
import {uuid} from "akeneo-design-system";
import {isRemoveWhitespaceOperation} from "./RemoveWhitespaceOperationBlock";
import {OperationBlockProps} from "./OperationBlockProps";

const REFERENCE_ENTITY_SINGLE_LINK_REPLACEMENT = 'reference_entity_single_link_replacement';

type ReferenceEntitySingleLinkReplacementOperation = {
    uuid: string;
    type: typeof REFERENCE_ENTITY_SINGLE_LINK_REPLACEMENT;
    mapping: ReplacementValues;
};

const isReferenceEntitySingleLinkReplacement = (operation?: any): operation is ReferenceEntitySingleLinkReplacementOperation =>
    undefined !== operation &&
    'type' in operation &&
    REFERENCE_ENTITY_SINGLE_LINK_REPLACEMENT === operation.type &&
    'mapping' in operation &&
    isReplacementValues(operation.mapping);

const getDefaultReferenceEntitySingleLinkReplacementOperation = (): ReferenceEntitySingleLinkReplacementOperation => ({
    uuid: uuid(),
    type: REFERENCE_ENTITY_SINGLE_LINK_REPLACEMENT,
    mapping: {},
});

const ReferenceEntitySingleLinkReplacementOperationBlock = ({
    operation,
    previewData,
    isLastOperation,
    onChange,
    onRemove,
}: OperationBlockProps) => {
    if (!isReferenceEntitySingleLinkReplacement(operation)) {
        throw new Error('RemoveWhitespaceOperationBlock can only be used with RemoveWhitespaceOperation');
    }

    return (
        <div>pouet</div>
    );
}

export {
    ReferenceEntitySingleLinkReplacementOperationBlock,
    getDefaultReferenceEntitySingleLinkReplacementOperation,
    REFERENCE_ENTITY_SINGLE_LINK_REPLACEMENT,
};
export type {ReferenceEntitySingleLinkReplacementOperation};
