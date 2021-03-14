const regexIsOverflowScrollable = /(visible|auto|scroll)/;
const isElementOverflowing = (el: Element): boolean => el.scrollHeight > el.clientHeight;
const isElementScrollable = (el: Element): boolean => regexIsOverflowScrollable.test(getComputedStyle(el).overflowY);

const findParents = (node: Element, parents: Element[]): Element[] => {
    if (null === node.parentNode) {
        return parents;
    }
    return findParents(node.parentNode as Element, parents.concat([node]));
};

const findScrollParent = (element: HTMLElement|null): Element => {
    if (null === element || null === element.parentNode) {
        return document.scrollingElement || document.documentElement;
    }

    const parents = findParents(element.parentNode as Element, [])
        .filter(parent => isElementScrollable(parent));

    return parents.find(parent => isElementOverflowing(parent))
        || (parents.length > 0 ? parents[0] : undefined)
        || document.scrollingElement
        || document.documentElement;
};

export default findScrollParent;
