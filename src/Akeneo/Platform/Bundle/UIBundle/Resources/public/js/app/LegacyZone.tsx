import React, {useLayoutEffect, useRef} from 'react';
import {LegacyView} from './bootstrap';
import {runIsolated} from './runIsolated';

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
      // A failing extension must neither unmount the application nor prevent the next ones from rendering
      runIsolated(() => view.render());
    });

    return () => views.forEach(view => view.remove());
  }, [views]);

  return <div data-drop-zone={code} ref={zoneRef} />;
});

export {LegacyZone};
