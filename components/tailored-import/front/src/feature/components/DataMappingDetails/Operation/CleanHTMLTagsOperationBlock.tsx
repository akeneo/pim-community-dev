import React from 'react';
import {Block, CloseIcon, IconButton} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/shared';
import {OperationBlockProps} from './OperationBlockProps';

const CLEAN_HTML_TAGS_TYPE = 'clean_html_tags';

type CleanHTMLTagsOperation = {
  type: typeof CLEAN_HTML_TAGS_TYPE;
};

const getDefaultCleanHTMLTagsOperation = (): CleanHTMLTagsOperation => ({
  type: CLEAN_HTML_TAGS_TYPE,
});

const CleanHTMLTagsOperationBlock = ({operation, onRemove}: OperationBlockProps) => {
  const translate = useTranslate();

  return (
    <Block
      action={
        <IconButton
          title={translate('pim_common.remove')}
          icon={<CloseIcon />}
          onClick={() => onRemove(operation.type)}
        />
      }
    >
      {translate(`akeneo.tailored_import.data_mapping.operations.${operation.type}`)}
    </Block>
  );
};

export {CLEAN_HTML_TAGS_TYPE, CleanHTMLTagsOperationBlock, getDefaultCleanHTMLTagsOperation};
export type {CleanHTMLTagsOperation};
