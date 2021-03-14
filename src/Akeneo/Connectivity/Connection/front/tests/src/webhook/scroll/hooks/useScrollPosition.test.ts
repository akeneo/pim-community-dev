import {renderHook} from '@testing-library/react-hooks';
import {fireEvent} from '@testing-library/dom';
import useScrollPosition, {ScrollPosition} from '@src/webhook/scroll/hooks/useScrollPosition';

beforeEach(() => {
    document.body.innerHTML = `
    <div>
        <div id='content'></div>
    </div>
    `;
});

test('The callback is called when scrolling', async (done) => {
    const ref = {
        current: document.getElementById('content'),
    };

    const callback = jest.fn().mockImplementation((position: ScrollPosition) => {
        // In jest, scroll positions are not calculated. It will always returns 0.
        // At least, we are checking that the callback is called with the expected object.
        expect(position.scrollHeight).toBe(0);
        done();
    });

    renderHook(() => useScrollPosition(ref, callback, [], 0));
    fireEvent.scroll(document.body, {target: {scrollY: 100}});
});
