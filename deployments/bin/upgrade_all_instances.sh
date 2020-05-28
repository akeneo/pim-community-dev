#!/bin/bash
#Must have in envar
#  $AREA : region to upgrade among [us|europe|asia]
#  $CLOUD_CUSTOMERS_DIR : where the directory "saas/project" is, exemple CLOUD_CUSTOMERS_DIR=~/cloud-customers/
#
# exemple : AREA=europe CLOUD_CUSTOMERS_DIR=~/code/cloudCustomers bash $0
#
LOGDIR=/tmp/deploy/log
GLOBALLOGFILE=${LOGDIR}/deploy.log

usage () {
	echo -e "USAGE: bash $0"
	echo "  Must have in envar :"
	echo "   AREA: Select region to upgrade among [us|europe|asia]"
	echo "   CLOUD_CUSTOMERS_DIR : where the directory 'saas/project' is"
	echo -e "\nexemple : AREA=europe CLOUD_CUSTOMERS_DIR=~/code/cloudCustomers bash $0"
	echo ""
	if [ ! -z "$1" ]; then
		echo " >> $1" |tee -a ${GLOBALLOGFILE}
	fi
	exit 99
}

upgrade_in_error () {
	migration_path=$1
	echo "ERROR : upgrade of ${migration_path} " >> ${LOGDIR}/${instanceName}.log
	echo -e "  ${RED}ERROR${DEFAULT}"
	echo "  ERROR" >> ${GLOBALLOGFILE}
}

upgrade_success () {
	migration_path=$1
	echo "OK : upgrade of ${migration_path}" >> ${LOGDIR}/${instanceName}.log
	echo -e "  ${GREEN}OK${DEFAULT}"
	echo "  OK" >> ${GLOBALLOGFILE}
}

run_migration () {
	migration_path=$1
	google_project_zone=$2
	migration_date=$(date '+%Y-%m-%d_%H-%M-%S')
	echo "[${instanceName}][${google_project_zone}] V4 UPGRADE" >> ${GLOBALLOGFILE}
	echo -e "[${BOLD}${instanceName}${DEFAULT}][${google_project_zone}] V4 UPGRADE"
	echo -e "PIM UPGRADER (AREA $AREA) - $(date '+%Y-%m-%d_%H-%M-%S')\n with directory : ${migration_path}" >> ${LOGDIR}/${instanceName}.log

	bash ${DIR}/upgrade_unit.sh ${migration_path} >> ${LOGDIR}/${instanceName}.log 2>&1 && upgrade_success ${migration_path} || upgrade_in_error ${migration_path}
}

upgrade_serenity () {
    # init files
    DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" >/dev/null 2>&1 && pwd )"
    [ ! -f ${DIR}/upgrade_unit.sh ] && usage "ERR : upgrade_unit.sh must exist in the same directory as this script."
    mkdir -p ${LOGDIR}; touch ${GLOBALLOGFILE}
	if [ -z "$AREA" ] || [ -z "$CLOUD_CUSTOMERS_DIR" ]; then
		usage "ERR: 2 parameters needed"
	fi
	echo -e "PIM UPGRADER (AREA $AREA) - $(date '+%Y-%m-%d_%H-%M-%S')" >> ${GLOBALLOGFILE}
	# get instance name in cloud-customers for the selected AREA
	for path_by_aera in $(find "${CLOUD_CUSTOMERS_DIR}/saas/projects" -name ${AREA}*); do
		for path in $(find "${path_by_aera}" -name 'srnt-*' -print); do
			google_project_zone=$(echo ${path}| rev | cut -f2 -d"/" | rev)
			instanceName=$(echo $path|awk -F'/' '{print $NF}')
			# if instance version is 3.2, continue with next instance
			if ! grep -q "pim-enterprise-dev" ${path}/main.tf; then
				echo "[${instanceName}][${google_project_zone}] NOT UPGRADABLE v3.2" | tee -a ${GLOBALLOGFILE}
				continue
			fi
			run_migration $path $google_project_zone
		done
	done
}

# ANSI colors
DEFAULT='\033[0m';BOLD='\033[1m';RED='\033[00;31m';GREEN='\033[00;32m'

upgrade_serenity
