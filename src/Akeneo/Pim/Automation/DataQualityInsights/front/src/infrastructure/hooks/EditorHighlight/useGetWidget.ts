import {useSelector} from 'react-redux';
import {ProductEditFormState} from '../../store';

const useGetWidget = (widgetId: string | null) => {
  return useSelector((state: ProductEditFormState) => {
    if (!widgetId) {
      return null;
    }

    return state.editorHighlight.widgets[widgetId] || null;
  });
};

export default useGetWidget;
