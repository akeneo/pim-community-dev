#!/bin/bash
set -e -o pipefail
TF_INPUT_FALSE="-input=false"
TF_AUTO_APPROVE="-auto-approve"

usage () {
	echo "This script to run terraform helm upgrade with the latest release"
	echo "USAGE : $0 PATH [RELEASE_TAG]"
	echo "  PATH :full path to access to main.tf/values.yaml of the instance"
	echo "  RELEASE_TAG : (optional) the tag of the release (ex: v20200228020157). If not set, the latest is used"
	echo "  File 'pre_upgrade.sh' must be in the same directory as this script"
	echo $1
	exit 99
}
if [ "$1" == "usage" ]; then
	usage
fi

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" >/dev/null 2>&1 && pwd )"
[ ! -f ${DIR}/pre_upgrade.sh ] && usage "ERR : pre_upgrade.sh must exist in the same directory as this script."
instance_dir_path=$1

if [ -z "$instance_dir_path" ]; then
	echo "ERROR : You must specify the instance directory path from / (where a stored main.tf & values.yaml)."
	exit 1
fi
if [ -z "$2" ]; then
	# Get latest tag / docker image name
	tag_to_release=$(git fetch origin &> /dev/null && git tag --list | grep -E '^v?[0-9]+$' | sort -r | head -n 1)
else
	# We have a tag in parameters, test if all is good
	custom_tag_to_release=$2
	tag_to_release=$(git fetch origin &> /dev/null && git tag --list | grep ${custom_tag_to_release})
	if [ -z ${tag_to_release} ]; then
		echo "ERROR : Release ${custom_tag_to_release} in parameter does not exist, exiting."
		exit 1
	fi
fi
# Get instance_name from cloud_customers path
instance_name=$(echo ${instance_dir_path}|awk -F'/' '{print $NF}')
echo "** upgrade ${instance_name} env with release tag : ${tag_to_release} **"

# Change TF files with the latest release
sed -i -E 's/v[0-9]{14}/'${tag_to_release}'/g' ${instance_dir_path}/main.tf

# Run pre-upgrade script
bash ${DIR}/pre_upgrade.sh

# Upgrade PIM
pushd . > /dev/null
cd ${instance_dir_path}
terraform init -upgrade ${TF_INPUT_FALSE}
terraform apply ${TF_INPUT_FALSE} ${TF_AUTO_APPROVE}
popd > /dev/null
