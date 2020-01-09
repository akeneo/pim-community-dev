import {RefObject, useEffect} from "react";
import {useDispatch, useSelector} from "react-redux";
import {debounce} from "lodash";
import {ProductEditFormState} from "../../store";
import {hidePopoverAction, initializePopoverOpeningAction, showPopoverAction} from "../../reducer";
import {MistakeElement} from "../../../domain";

const OPENING_MILLISECONDS_DELAY = 50;
const CLOSING_MILLISECONDS_DELAY = 300;

const useGetPopover = () => {
  const {popover} = useSelector((state: ProductEditFormState) => state.spellcheck);
  const dispatchAction = useDispatch();

  useEffect(() => {
    const handleOpening = debounce((mistake: MistakeElement, highlightRef: RefObject<Element>, callback?: Function) => {
      if (handleClosing && handleClosing.cancel) {
        handleClosing.cancel();
      }

      if (callback) {
        callback();
      }

      dispatchAction(showPopoverAction(mistake, highlightRef));
    }, OPENING_MILLISECONDS_DELAY);

    const handleClosing = debounce((callback?: Function) => {
      if (handleOpening && handleOpening.cancel) {
        handleOpening.cancel();
      }

      if (callback) {
        callback();
      }

      dispatchAction(hidePopoverAction());
    }, CLOSING_MILLISECONDS_DELAY);

    dispatchAction(initializePopoverOpeningAction(handleOpening, handleClosing));
  }, []);

  return popover;
};

export default useGetPopover;
