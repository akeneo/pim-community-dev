import React, {useEffect, useState} from 'react';
import {mediator} from '@akeneo-pim-community/shared';

/**
 * Overlay displayed behind the side panels. Panels show / hide it through the mediator, a click on it asks them to
 * close.
 */
const Overlay = () => {
  const [isVisible, setVisible] = useState<boolean>(false);

  useEffect(() => {
    const show = () => setVisible(true);
    const hide = () => setVisible(false);
    mediator.on('pim-app:overlay:show', show);
    mediator.on('pim-app:overlay:hide', hide);

    return () => {
      mediator.off('pim-app:overlay:show', show);
      mediator.off('pim-app:overlay:hide', hide);
    };
  }, []);

  return (
    <div
      id="overlay"
      className={`AknOverlay${isVisible ? ' AknOverlay--show' : ''}`}
      onClick={() => mediator.trigger('pim-app:panel:close')}
    />
  );
};

export {Overlay};
