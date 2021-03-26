import {DependencyList, EffectCallback, useEffect, useRef} from 'react';

const useEffectAfterFirstRender = (effect: EffectCallback, deps?: DependencyList): void => {
    const isInitialized = useRef(false);

    useEffect(() => {
        if (!isInitialized.current) {
            isInitialized.current = true;
            return;
        }

        effect();
    }, deps);
};

export {useEffectAfterFirstRender};
