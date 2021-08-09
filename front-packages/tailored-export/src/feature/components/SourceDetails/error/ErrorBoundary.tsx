import React from 'react';
import {
  InvalidAssociationTypeSourceError,
  InvalidAssociationTypeSourcePlaceholder,
  InvalidAttributeSourceError,
  InvalidAttributeSourcePlaceholder,
  InvalidPropertySourceError,
  InvalidPropertySourcePlaceholder,
} from '../error';

type ErrorBoundaryProps = {};

class ErrorBoundary extends React.Component<ErrorBoundaryProps, {error: Error | null}> {
  constructor(props: ErrorBoundaryProps) {
    super(props);

    this.state = {error: null};
  }

  static getDerivedStateFromError(error: Error) {
    return {error};
  }

  render() {
    const {error} = this.state;

    if (null === error) return this.props.children;

    switch (true) {
      case error instanceof InvalidAttributeSourceError:
        return <InvalidAttributeSourcePlaceholder />;
      case error instanceof InvalidAssociationTypeSourceError:
        return <InvalidAssociationTypeSourcePlaceholder />;
      case error instanceof InvalidPropertySourceError:
        return <InvalidPropertySourcePlaceholder />;
      default:
        return <div>{error.message}</div>;
    }
  }
}

export {ErrorBoundary};
