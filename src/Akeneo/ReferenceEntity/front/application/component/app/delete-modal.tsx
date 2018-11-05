import * as React from 'react';
import __ from 'akeneoreferenceentity/tools/translator';
import Key from 'akeneoreferenceentity/tools/key';

interface Props {
  message: string;
  title: string;
  onConfirm: () => void;
  onCancel: () => void;
}

class DeleteModal extends React.Component<Props> {
  private cancelButton: React.RefObject<HTMLButtonElement>;

  constructor(props: Props) {
    super(props);

    this.cancelButton = React.createRef();
  }

  componentDidMount() {
    if (null !== this.cancelButton.current) {
      this.cancelButton.current.focus();
    }
  }

  render () {
    const {
      message,
      title,
      onConfirm,
      onCancel,
    } = this.props;

    return (
      <div className="modal modal--fullPage in" aria-hidden="false" style={{zIndex: 1041}}>
        <div className="AknFullPage AknFullPage--modal AknFullPage--confirm">
          <div className="AknFullPage-content AknFormContainer--withPadding AknFormContainer--centered AknFormContainer--expanded">
            <div className="AknFullPage-left">
              <div className="AknFullPage-image AknFullPage-illustration AknFullPage-illustration--delete" />
            </div>
            <div className="AknFullPage-right">
              <div className="AknFullPage-subTitle">{title}</div>
              <div className="AknFullPage-title">{__('pim_reference_entity.modal.delete.subtitle')}</div>
              <div className="AknFullPage-description AknFullPage-description--bottom">{message}</div>
              <div className="AknButtonList">
                <button
                  ref={this.cancelButton}
                  className="AknButtonList-item AknButton AknButton--grey cancel"
                  onKeyPress={(event: React.KeyboardEvent<HTMLButtonElement>) => {
                    if (Key.Space === event.key) onCancel()
                  }}
                  onClick={onCancel}
                >
                  {__('pim_reference_entity.modal.delete.button.cancel')}
                </button>

                <button className="AknButtonList-item AknButton AknButton--important ok" onClick={onConfirm}>
                  {__('pim_reference_entity.modal.delete.button.confirm')}
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    );
  }
}

export default DeleteModal;
