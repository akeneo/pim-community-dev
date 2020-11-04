import {useEffect} from 'react';
import {useDispatch, useSelector} from 'react-redux';
import {debounce} from 'lodash';
import {ProductEditFormState} from '../../store';
import {
  disableWidgetHighlightAction,
  enableWidgetHighlightAction,
  hidePopoverAction,
  initializePopoverOpeningAction,
  showPopoverAction,
} from '../../reducer';
import {HighlightElement, WidgetElement} from '../../../application/helper';

const OPENING_MILLISECONDS_DELAY = 50;
const CLOSING_MILLISECONDS_DELAY = 500;

const useGetPopover = () => {
  const {popover} = useSelector((state: ProductEditFormState) => state.editorHighlight);
  const dispatchAction = useDispatch();

  useEffect(() => {
    const handleOpening = debounce((widget: WidgetElement, highlight: HighlightElement, callback?: Function) => {
      if (handleClosing && handleClosing.cancel) {
        handleClosing.cancel();
      }

      if (callback) {
        callback();
      }

      if (!highlight.isActive) {
        dispatchAction(enableWidgetHighlightAction(widget.id, highlight.id));
      }

      dispatchAction(showPopoverAction(widget.id, highlight));
    }, OPENING_MILLISECONDS_DELAY);

    const handleClosing = debounce((callback?: Function) => {
      if (handleOpening && handleOpening.cancel) {
        handleOpening.cancel();
      }

      if (callback) {
        callback();
      }

      dispatchAction(disableWidgetHighlightAction());
      dispatchAction(hidePopoverAction());
    }, CLOSING_MILLISECONDS_DELAY);

    dispatchAction(initializePopoverOpeningAction(handleOpening, handleClosing));
  }, []);

  return popover;
};

export default useGetPopover;
