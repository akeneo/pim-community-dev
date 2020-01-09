import {useEffect} from "react";
import {useDispatch} from "react-redux";
import {createHighlight, EditorElement, HighlightElement, MistakeElement, WidgetElement} from "../../../domain";
import {updateWidgetHighlightsAction} from "../../reducer";

const generateHighlights = async (mistakes: MistakeElement[], element: EditorElement) => {
  return new Promise<HighlightElement[]>((resolve) => {
    const highlights = mistakes.map(mistake => {
      return createHighlight(mistake, element);
    });

    return resolve(highlights);
  });
};

const useGetHighlights = (widget: WidgetElement, clonedEditor: EditorElement | null = null) => {
  const dispatchAction = useDispatch();
  const {id, editor, highlights, analysis} = widget;

  useEffect(() => {
    (async () => {
      const highlights = await generateHighlights(
        analysis,
        clonedEditor || editor
      );
      dispatchAction(updateWidgetHighlightsAction(id, highlights));
    })();
  }, [id, analysis, editor.id, clonedEditor, dispatchAction]);

  return highlights;
};

export default useGetHighlights;
