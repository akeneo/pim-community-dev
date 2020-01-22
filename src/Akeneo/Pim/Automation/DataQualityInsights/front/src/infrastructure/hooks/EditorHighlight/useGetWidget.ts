import {useSelector} from "react-redux";
import {ProductEditFormState} from "../../store";

const useGetWidget= (widgetId: string | null) => {
  if (!widgetId) {
    return null;
  }

  return useSelector((state: ProductEditFormState) => {
    return state.editorHighlight.widgets[widgetId] || null
  });
};

export default useGetWidget;
