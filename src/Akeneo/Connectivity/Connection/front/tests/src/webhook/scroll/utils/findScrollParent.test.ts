import findScrollParent from '@src/webhook/scroll/utils/findScrollParent';

test('It returns the default scrolling element when the element is null', () => {
    const parent = findScrollParent(null);

    expect(parent.tagName).toBe('HTML');
});

test('It returns the default scrolling element when there is not overflow configured', () => {
    document.body.innerHTML = `
    <div>
        <div id='content'></div>
    </div>
    `;

    const element = document.getElementById('content');
    const parent = findScrollParent(element);

    expect(parent.tagName).toBe('HTML');
});

test('It returns the first scrollable parent', () => {
    document.body.innerHTML = `
    <div id='other-scrollable-parent' style="overflow-y: visible;">
        <div id='first-scrollable-parent' style="overflow-y: visible;">
            <div id='content'></div>
        </div>
    </div>
    `;

    const element = document.getElementById('content');
    const parent = findScrollParent(element);

    expect(parent.id).toBe('first-scrollable-parent');
});
