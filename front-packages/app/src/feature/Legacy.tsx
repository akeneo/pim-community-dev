import React from 'react';
import {useEffect} from 'react';
import {useRef} from 'react';

const loadScript = async (scriptUrl: string): Promise<void> => {
  return new Promise(resolve => {
    const vendorScript = document.createElement('script');
    vendorScript.onload = function () {
      resolve();
    };
    vendorScript.src = scriptUrl;

    document.head.appendChild(vendorScript);
  });
};

const Legacy = () => {
  const ref = useRef<HTMLDivElement>(null);

  useEffect(() => {
    if (null === ref.current) return;
    (async () => {
      await Promise.all([loadScript('./dist/vendor.min.js'), loadScript('./dist/main.min.js')]);
      console.log('mount');

      (window as any).pimLegacy.build('pim-app').then((form: any) => {
        form.setElement(ref.current);
        form.render();
      });
    })();
  }, []);

  return <div className="app" ref={ref}></div>;
};

export {Legacy};
