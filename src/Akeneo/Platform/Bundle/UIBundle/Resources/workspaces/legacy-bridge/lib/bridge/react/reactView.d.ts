import BaseView = require('pimui/js/view/base');
declare abstract class ReactView extends BaseView {
    abstract reactElementToMount(): JSX.Element;
    render(): BaseView;
    remove(): BaseView;
}
export { ReactView };
