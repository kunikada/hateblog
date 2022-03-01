#!/usr/bin/env bash
set -Ceuo pipefail
 
export LC_ALL=C
export LANG=C
 
function usage() {
  echo "Usage:"
  echo "  $(basename $BASH_SOURCE) [OPTIONS]"
  echo
  echo "Options:"
  echo "  --backup"
  echo "    Backup data volume."
  echo "  --restore"
  echo "    Restore data volume."
}
 
function cecho() {
  local color_name color
  readonly color_name="$1"
  shift
  case $color_name in
    red) color=31 ;;
    green) color=32 ;;
    yellow) color=33 ;;
    blue) color=34 ;;
    cyan) color=36 ;;
    *) error_exit "An undefined color was specified." ;;
  esac
  printf "\033[${color}m%b\033[m\n" "$*"
}
 
function error_exit() {
  {
    cecho red "[ERROR] $1"
    echo
    usage
  } 1>&2
  exit 1
}
 
declare -a ARGS=()
 
IS_DEBUG="false"
ENV="production"
MODE=
 
while (($# > 0)); do
  case "$1" in
    -h | --help)
      usage
      exit 0
      ;;
    --debug)
      IS_DEBUG="true"
      shift
      ;;
    --env)
      if (($# < 2)) || [[ $2 =~ ^-+ ]]; then
        error_exit "Optional arguments are required -- $1"
      fi
      ENV=$2
      shift 2
      ;;
    --backup)
      MODE="backup"
      shift
      ;;
    --restore)
      MODE="restore"
      shift
      ;;
    --)
      shift
      while (($# > 0)); do
        ARGS+=("$1")
        shift
      done
      break
      ;;
    -*)
      error_exit "Illegal option -- '$(echo $1 | perl -pe 's/^-*//')'"
      ;;
    *)
      if [[ $1 != "" ]] && [[ ! $1 =~ ^-+ ]]; then
        ARGS+=("$1");
      fi
      shift
      ;;
  esac
done
 
#if ((${#ARGS[@]} < 2)); then
#  error_exit "Insufficient number of script arguments."
#fi
 
if [[ ! $ENV =~ ^(production|staging)$ ]]; then
  error_exit "Invalid argument of --env option -- $ENV"
fi
 
if [[ ! $MODE =~ ^(backup|restore)$ ]]; then
  error_exit "Invalid argument of mode option -- $MODE"
fi
 
readonly SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$SCRIPT_DIR"
 
if [[ $IS_DEBUG == "true" ]]; then
  echo "Dump options:"
  echo "  ENV=${ENV}"
  echo "  IS_DEBUG=${IS_DEBUG}"
  echo "  MODE=${MODE}"
  echo
  echo "Dump arguments:"
  for ((i = 0; i < ${#ARGS[@]}; i++)); do
    echo '  $'"$(($i + 1))=${ARGS[$i]}"
  done
  echo
fi
 
function backup_process() {
  docker run --rm --volumes-from hateblog_db -v $(pwd):/tmp alpine tar cvfz /tmp/dbbackup.tgz /var/lib/mysql
}

function restore_process() {
  docker run --rm --volumes-from hateblog_db -v $(pwd):/tmp alpine bash -c "cd / && tar xvfz /tmp/backup.tgz"
}
 
function debug_process() {
  echo "Debug:"
}
 
function on_exit() {
  local exit_code=$1
  exit $exit_code
}
 
function main() {
  trap 'on_exit $?' EXIT
 
  if [[ $MODE == "backup" ]]; then
    backup_process
  fi
 
  if [[ $MODE == "restore" ]]; then
    restore_process
  fi

  if [[ $IS_DEBUG == "true" ]]; then
    debug_process
  fi
}
 
main
