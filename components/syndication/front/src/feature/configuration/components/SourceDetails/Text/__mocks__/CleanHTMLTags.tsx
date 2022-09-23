import React from 'react';
import {CleanHTMLTagsOperation} from '../CleanHTMLTags';

const CleanHTMLTags = ({
  onOperationChange,
}: {
  onOperationChange: (updatedOperation: CleanHTMLTagsOperation) => void;
}) => (
  <button
    onClick={() =>
      onOperationChange({
        type: 'clean_html_tags',
        value: true,
      })
    }
  >
    Clean HTML tags
  </button>
);

export {CleanHTMLTags};
