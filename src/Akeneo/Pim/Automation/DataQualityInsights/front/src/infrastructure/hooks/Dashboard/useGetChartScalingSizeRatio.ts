import {useEffect, useLayoutEffect, useState, RefObject} from "react";

const useGetChartScalingSizeRatio = (chartContainerRef: RefObject<Element|null>, initialWidth: number) => {
  const [scaleRatio, setScaleRatio] = useState(1);

  useEffect(() => {
    let lastScaleRatio = 1;
    let ticking = false;

    const handleResize = () => {
      if (!chartContainerRef.current) {
        return;
      }

      const rect = chartContainerRef.current.getBoundingClientRect();
      lastScaleRatio = 1;
      if (rect.width > initialWidth) {
        lastScaleRatio = (rect.width/ initialWidth);
      }

      if (!ticking) {
        window.requestAnimationFrame(function() {
          setScaleRatio(lastScaleRatio);
          ticking = false;
        });
        ticking = true;
      }
    };
    window.addEventListener("resize", handleResize);

    return () => {
      ticking = true;
      window.removeEventListener("resize", handleResize);
    };
  }, []);

  useLayoutEffect(() => {
    if (chartContainerRef.current) {
      const rect = chartContainerRef.current.getBoundingClientRect();

      if (rect.width > initialWidth) {
        setScaleRatio(rect.width/ initialWidth);
      }
    }
  }, [chartContainerRef, initialWidth]);

  return {
    upScalingRatio: scaleRatio,
    downScalingRatio: 1/scaleRatio
  }
};

export default useGetChartScalingSizeRatio;
