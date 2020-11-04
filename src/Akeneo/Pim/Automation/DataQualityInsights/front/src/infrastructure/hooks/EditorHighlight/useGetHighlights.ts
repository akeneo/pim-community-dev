import {useEffect} from 'react';
import {useDispatch} from 'react-redux';
import {
  createHighlight,
  EditorElement,
  HighlightElement,
  MistakeElement,
  WidgetElement,
} from '../../../application/helper';
import {updateWidgetHighlightsAction} from '../../reducer';

const uuidV5 = require('uuid/v5');

const generateHighlights = async (widgetId: string, mistakes: MistakeElement[], element: EditorElement) => {
  return new Promise<HighlightElement[]>(resolve => {
    const highlights = mistakes.map(mistake => {
      const identifier = uuidV5(`${mistake.text}-${mistake.globalOffset}`, widgetId);
      return createHighlight(identifier, mistake, element);
    });

    return resolve(highlights);
  });
};

const useGetHighlights = (widget: WidgetElement, clonedEditor: EditorElement | null = null) => {
  const dispatchAction = useDispatch();
  const {id, editor, highlights, analysis} = widget;

  useEffect(() => {
    (async () => {
      const highlights = await generateHighlights(id, analysis, clonedEditor || editor);
      dispatchAction(updateWidgetHighlightsAction(id, highlights));
    })();
  }, [id, analysis, editor.id, clonedEditor, dispatchAction]);

  return highlights;
};

export default useGetHighlights;
