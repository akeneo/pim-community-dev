import {useSelector} from 'react-redux';
import {ProductEditFormState} from '../../store';

const useGetWidgetsList = () => {
  const {widgets} = useSelector((state: ProductEditFormState) => state.editorHighlight);

  return widgets;
};

export default useGetWidgetsList;
