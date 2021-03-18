import {DependencyList, EffectCallback, useCallback, useEffect, useRef, useState} from 'react';

const useEffectAfterFirstRender = (effect: EffectCallback, deps?: DependencyList): void => {
    const isInitialized = useRef(false);

    useEffect(() => {
        if (!isInitialized.current) {
            isInitialized.current = true;
            return;
        }

        effect();
    }, [effect, deps]);
};

export {useEffectAfterFirstRender};
