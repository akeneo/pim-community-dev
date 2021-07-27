export type ScrollPosition = {
    scrollTop: number;
    clientHeight: number;
    scrollHeight: number;
};

export const getScrollPosition = (element: Element): ScrollPosition => {
    const {scrollTop, clientHeight, scrollHeight} = element;

    return {scrollTop, clientHeight, scrollHeight};
};
