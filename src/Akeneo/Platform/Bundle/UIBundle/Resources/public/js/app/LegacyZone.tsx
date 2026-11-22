import React, {useLayoutEffect, useRef} from 'react';
import {LegacyView} from './bootstrap';

type LegacyZoneProps = {
  code: string;
  views: LegacyView[];
};

/**
 * Drop zone rendering legacy (Backbone) views registered in the application shell.
 * Views are appended then rendered, exactly like the former base form did for its extensions.
 */
const LegacyZone = React.memo(({code, views}: LegacyZoneProps) => {
  const zoneRef = useRef<HTMLDivElement>(null);

  useLayoutEffect(() => {
    const zone = zoneRef.current as HTMLDivElement;
    views.forEach(view => {
      zone.appendChild(view.el);
      view.render();
    });

    return () => views.forEach(view => view.remove());
  }, [views]);

  return <div data-drop-zone={code} ref={zoneRef} />;
});

export {LegacyZone};
