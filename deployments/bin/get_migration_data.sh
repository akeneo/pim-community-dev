#!/bin/bash

PARAM_PROJECT="*"
PARAM_PFID="*"
PARAM_MIGRATION="*"

PARAM_MAX_VALUE=${PARAM_MAX_VALUE}
PARAM_P90=${PARAM_P90}
PARAM_QUERY=${PARAM_QUERY}
PARAM_UNIT=${PARAM_UNIT}
PARAM_OPERATION=${OPERATION}

TO_TS=${TO_TS:-$(date +%s)}
FROM_TS=${FROM_TS:-$(( TO_TS - 60*60*24*7 ))}

function help() {
  echo "Get migration timings and memory consumption"
  echo ""
  echo "Usage : $(basename $0) [options] -o timings|memory"
  echo "  -o <value>| --operation=<value> : Operation to do (timings or memory)"
  echo ""
  echo "option :"
  echo "  -p <value>| --project=<value>   : [optional] Limit data recuperation to given project (e.g.: akecld-saas-dev)"
  echo "  -i <value>| --instance=<value>  : [optional] of your instance (e.g.: srnt-c3po)"
  echo "  -m <value>| --max_value=<value> : [optional] max value of your timing or memory consumption (set in ms for timings and MB for memory)"
  echo "  --p90=<value>                   : [optional] p90 max  value of your timing or memory consumption (set in ms for timings and MB for memory)"
  echo ""
}

function die() {
  echo "$*" >&2;
  exit 2;
}

function needs_arg() {
  if [ -z "$OPTARG" ]; then
    die "No arg for --$OPT option";
  fi;
}

function setDefaultValues() {
  case $1 in
    timings)
      METRIC="max:pim.migration.duration_ms"
      PARAM_MAX_VALUE=${PARAM_MAX_VALUE:-$(( 60*1000 ))}
      PARAM_P90=${PARAM_P90:-$PARAM_MAX_VALUE}
      PARAM_UNIT="ms"
      ;;
    memory)
      METRIC="max:pim.migration.memory_mb"
      PARAM_MAX_VALUE=${PARAM_MAX_VALUE:-128}
      PARAM_P90=${PARAM_P90:-$PARAM_MAX_VALUE}
      PARAM_UNIT="MB"
      ;;
    *)
      help
      die "Only \"timings\" or \"memory\" are accepted as operation value"
      exit 1
      ;;
  esac

  PARAM_QUERY="${METRIC}{kube_namespace:${PARAM_PFID},project:${PARAM_PROJECT},migration.name:${PARAM_MIGRATION,,}}%20by%20{migration.name}"
}

while getopts hp:i:m:o:-: OPT; do
  # support long options: https://stackoverflow.com/a/28466267/519360
  if [ "$OPT" = "-" ]; then   # long option: reformulate OPT and OPTARG
    OPT="${OPTARG%%=*}"       # extract long option name
    OPTARG="${OPTARG#$OPT}"   # extract long option argument (may be empty)
    OPTARG="${OPTARG#=}"      # if long option argument, remove assigning `=`
  fi
  case "$OPT" in
    h | help )
      help
      exit 0
      ;;
    o | operation )
      needs_arg
      PARAM_OPERATION="$OPTARG"
      ;;
    p | project )
      needs_arg
      PARAM_PROJECT="$OPTARG"
      ;;
    i | instance )
      needs_arg;
      PARAM_PFID="$OPTARG"
      ;;
    m | migration )
      needs_arg;
      PARAM_MIGRATION=${OPTARG}
      ;;
    a | max_value )
      needs_arg;
      PARAM_MAX_VALUE="$OPTARG"
      ;;
    g | p90 )
      needs_arg;
      PARAM_P90="$OPTARG"
      ;;
    ??* )          die "Illegal option --$OPT" ;;  # bad long option
    ? )            exit 2 ;;  # bad short option (error reported via getopts)
  esac
done
shift $((OPTIND-1)) # remove parsed options and args from $@ list

setDefaultValues $PARAM_OPERATION

if [[ -z "${DATADOG_API_KEY}" || -z "${DATADOG_APP_KEY}" ]]; then
  help
  die "Environment variables DATADOG_API_KEY and DATADOG_APP_KEY are mandatory"
fi

if [[ -z "${PARAM_OPERATION}" ]]; then
  help
  die "operation parameter is mandatory with '-o <value>' or '--operation=<value>'"
fi

echo "Parameters :"
echo "  Operation : ${PARAM_OPERATION}"
echo "  Project : ${PARAM_PROJECT}"
echo "  Instance : ${PARAM_PFID}"
echo "  Migration : ${PARAM_MIGRATION}"
echo "  Query : ${PARAM_QUERY}"
echo "  Max value : ${PARAM_MAX_VALUE}"
echo "  P90 : ${PARAM_P90}"
echo "  Unit : ${PARAM_UNIT}"
echo ""

# Get query values
DATA=$(curl --location -s -g -H "Content-Type: application/json" -H "DD-API-KEY: ${DATADOG_API_KEY}" -H "DD-APPLICATION-KEY: ${DATADOG_APP_KEY}" --request GET "https://api.datadoghq.eu/api/v1/query?from=${FROM_TS}&to=${TO_TS}&query=${PARAM_QUERY}")

ERROR=0
COUNT=0
for PARSED_DATA in $(echo ${DATA} | jq -r '.series[] | .tag_set[0] + "#" + (.pointlist[0][1]|tostring)'); do
  ((COUNT++))
  NAME=$(echo ${PARSED_DATA} | cut -d "#" -f1 | cut -d ":" -f2)
  VALUE=$(echo ${PARSED_DATA} | cut -d "#" -f2  | bc -l)
  COLOR="\e[32m"
  if (( $(echo "$VALUE > $PARAM_MAX_VALUE" |bc -l) )); then
    ERROR=1
    COLOR="\e[31m"
  fi
  echo -e "${COLOR}${NAME} : ${VALUE}${PARAM_UNIT}\e[0m"
done

if [[ ${ERROR} -eq 1 ]]; then
  echo -e "\e[31mAt least one migration takes more than ${PARAM_MAX_VALUE}${PARAM_UNIT}\e[0m"
fi

if [[ ${COUNT} -eq 0 ]]; then
  echo -e "\e[31mNo migration '"${PARAM_MIGRATION}"' found\e[0m"
fi

echo ""

exit ${ERROR}

