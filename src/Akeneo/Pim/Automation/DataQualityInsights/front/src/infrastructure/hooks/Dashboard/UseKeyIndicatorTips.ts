import {KeyIndicatorTips} from '../../../domain';
import {useKeyIndicatorsContext} from '../../../application/context/KeyIndicatorsContext';

const useGetKeyIndicatorTips = (keyIndicator: string): KeyIndicatorTips => {
  const {tips} = useKeyIndicatorsContext();

  if (!tips.hasOwnProperty(keyIndicator)) {
    return {};
  }

  return tips[keyIndicator];
};

export {useGetKeyIndicatorTips};
