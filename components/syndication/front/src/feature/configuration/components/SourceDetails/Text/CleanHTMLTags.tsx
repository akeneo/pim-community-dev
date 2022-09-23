import React, {useState} from 'react';
import {useTranslate} from '@akeneo-pim-community/shared';
import {Collapse, Checkbox, Pill} from 'akeneo-design-system';

const CLEAN_HTML_TAGS_OPERATION = 'clean_html_tags';

type CleanHTMLTagsOperation = {
  type: 'clean_html_tags';
  value: boolean;
};

const isCleanHTMLTagsOperation = (operation?: any): operation is CleanHTMLTagsOperation =>
  undefined !== operation &&
  'type' in operation &&
  CLEAN_HTML_TAGS_OPERATION === operation.type &&
  'value' in operation &&
  'boolean' === typeof operation.value;

const getDefaultCleanHTMLTagsOperation = (): CleanHTMLTagsOperation => ({
  type: CLEAN_HTML_TAGS_OPERATION,
  value: false,
});

const isDefaultCleanHTMLTagsOperation = (operation?: CleanHTMLTagsOperation) =>
  CLEAN_HTML_TAGS_OPERATION === operation?.type && !operation.value;

type CleanHTMLTagsProps = {
  operation?: CleanHTMLTagsOperation;
  onOperationChange: (updatedOperation?: CleanHTMLTagsOperation) => void;
};

const CleanHTMLTags = ({operation = getDefaultCleanHTMLTagsOperation(), onOperationChange}: CleanHTMLTagsProps) => {
  const [isCleanHTMLTagsCollapsed, toggleCleanHTMLTagsCollapsed] = useState<boolean>(false);
  const translate = useTranslate();

  return (
    <Collapse
      collapseButtonLabel={isCleanHTMLTagsCollapsed ? translate('pim_common.close') : translate('pim_common.open')}
      label={
        <>
          {translate('akeneo.syndication.data_mapping_details.sources.operation.clean_html_tags.title')}
          {!isDefaultCleanHTMLTagsOperation(operation) && <Pill level="primary" />}
        </>
      }
      isOpen={isCleanHTMLTagsCollapsed}
      onCollapse={toggleCleanHTMLTagsCollapsed}
    >
      <Checkbox
        checked={operation.value}
        onChange={checked => {
          const newOperation = {...operation, value: checked};
          onOperationChange(isDefaultCleanHTMLTagsOperation(newOperation) ? undefined : newOperation);
        }}
      >
        {translate('akeneo.syndication.data_mapping_details.sources.operation.clean_html_tags.label')}
      </Checkbox>
    </Collapse>
  );
};

export {CleanHTMLTags, isCleanHTMLTagsOperation, CLEAN_HTML_TAGS_OPERATION, isDefaultCleanHTMLTagsOperation};
export type {CleanHTMLTagsOperation};
