#!/usr/bin/env bash

set -eu

usage() {
    echo "Usage: $0 OLD_IMAGE_TAG"
    echo "The $OLD_IMAGE_TAG must be equivalent to CIRCLE_SHA1 in the release workflow."
    echo
    echo "Example:"
    echo "    $0 4ec70727d693fba9c953ea6af9f6c8fc1b99b61a"
    echo
    exit 1
}

if [ $# -ne 1 ]; then
    usage
    exit 1
fi

OLD_IMAGE_TAG=$1

docker pull eu.gcr.io/akeneo-ci/pim-enterprise-dev:${OLD_IMAGE_TAG}

# read the tag from the image that was built during the "build_prod" step
NEW_IMAGE_TAG=v$(docker run --rm eu.gcr.io/akeneo-ci/pim-enterprise-dev:${OLD_IMAGE_TAG} grep -o "const VERSION = '.*';$" src/Akeneo/Platform/EnterpriseVersion.php | sed "s/const VERSION = '//" | sed "s/';//")

echo Tagging Docker image ${NEW_IMAGE_TAG}
docker image tag eu.gcr.io/akeneo-ci/pim-enterprise-dev:${OLD_IMAGE_TAG} eu.gcr.io/akeneo-ci/pim-enterprise-dev:${NEW_IMAGE_TAG}
docker image tag eu.gcr.io/akeneo-ci/pim-enterprise-dev:${OLD_IMAGE_TAG} eu.gcr.io/akeneo-cloud/pim-enterprise-dev:${NEW_IMAGE_TAG}

echo Pushing Docker image ${NEW_IMAGE_TAG}
docker push eu.gcr.io/akeneo-cloud/pim-enterprise-dev:${NEW_IMAGE_TAG}
IMAGE_TAG=${NEW_IMAGE_TAG} make push-php-image-prod

echo Tagging EE dev repository
git push origin master
git tag -a ${NEW_IMAGE_TAG} -m "Tagging SaaS version ${NEW_IMAGE_TAG}"
git push origin ${NEW_IMAGE_TAG}
