# Prod tools
ARG CLOUD_SDK_VERSION=410.0.0
ARG HELM_VERSION=3.10.1
ARG YQ_VERSION=4.25.3

# Dev tools
ARG ARGOCD_VERSION=v2.5.0
ARG TERRAFORM_VERSION=1.1.3

FROM google/cloud-sdk:${CLOUD_SDK_VERSION}-alpine as base
FROM alpine/helm:${HELM_VERSION} as helm-source
FROM mikefarah/yq:${YQ_VERSION} as yq-source
FROM argoproj/argocd:${ARGOCD_VERSION} as argocd-source
FROM hashicorp/terraform:${TERRAFORM_VERSION} as terraform-source

##############
# Prod image #
##############
FROM base AS pim-deployer-prod
RUN gcloud components install kubectl gke-gcloud-auth-plugin --quiet && \
  rm -rf $(find google-cloud-sdk/ -regex ".*/__pycache__") && \
  rm -rf google-cloud-sdk/.install/.backup
COPY --from=helm-source /usr/bin/helm /usr/bin/helm
COPY --from=yq-source /usr/bin/yq /usr/bin/yq

CMD []

##############
# Dev image  #
##############
FROM pim-deployer-prod AS pim-deployer-dev
RUN apk add --no-cache curl
COPY --from=argocd-source /usr/local/bin/argocd /usr/bin/argocd
COPY --from=terraform-source /bin/terraform /usr/bin/terraform

CMD []
