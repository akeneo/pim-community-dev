export const copyTextToClipboard = (element: HTMLElement) => {
    const selection = window.getSelection();
    if (null === selection) {
        return;
    }

    const range = document.createRange();
    range.selectNodeContents(element);

    selection.removeAllRanges();
    selection.addRange(range);

    document.execCommand('copy');

    selection.removeAllRanges();
};
