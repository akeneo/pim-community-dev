import {useSelector} from "react-redux";
import {ProductEditFormState} from "../../store";

const useGetWidget= (widgetId: string | null) => {
  if (!widgetId) {
    return null;
  }

  const widget = useSelector((state: ProductEditFormState) => {
    return state.spellcheck.widgets[widgetId] || null
  });

  return widget;
};

export default useGetWidget;
