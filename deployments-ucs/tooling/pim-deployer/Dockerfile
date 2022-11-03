ARG CLOUD_SDK_VERSION=400.0.0
ARG HELM_VERSION=3.10.1
ARG YQ_VERSION=4.25.3

FROM alpine/helm:$HELM_VERSION as helm-source
FROM mikefarah/yq:$YQ_VERSION as yq-source

FROM google/cloud-sdk:$CLOUD_SDK_VERSION-alpine as pim-deployer
RUN gcloud components install kubectl gke-gcloud-auth-plugin --quiet
COPY --from=helm-source /usr/bin/helm /usr/bin/helm
RUN chmod +x /usr/bin/helm
COPY --from=yq-source /usr/bin/yq /usr/bin/yq
RUN chmod +x /usr/bin/yq

CMD []
