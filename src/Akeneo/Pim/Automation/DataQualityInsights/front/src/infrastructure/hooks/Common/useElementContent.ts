import {useCallback, useEffect, useState} from 'react';
import {convertHtmlContent, getEditorContent} from '../../../application/helper';
import EditorElement, {isEditableContent} from '../../../application/helper/EditorHighlight/EditorElement';

type ElementContentState = {
  content: string;
  analyzableContent: string;
  refresh: () => void;
};

const useElementContent = (element: HTMLElement): ElementContentState => {
  const [content, setContent] = useState<string>('');
  const [analyzableContent, setAnalyzableContent] = useState<string>('');

  const handleRefresh = useCallback(() => {
    setContent(getEditorContent(element as EditorElement));
  }, [element]);

  useEffect(() => {
    handleRefresh();
  }, [element, handleRefresh]);

  useEffect(() => {
    let text = content;

    if (isEditableContent(element as EditorElement)) {
      text = convertHtmlContent(content);
    }

    setAnalyzableContent(text);
  }, [element, content]);

  return {
    content,
    analyzableContent,
    refresh: handleRefresh,
  };
};

export default useElementContent;
