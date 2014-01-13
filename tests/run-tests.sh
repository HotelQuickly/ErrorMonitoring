#!/bin/bash

DIR=$(cd `dirname $0` && pwd)

if [ ! -f "${DIR}/run-config" ]
then
	echo "please copy run-config.template to run-config and make sure config is good."
	exit 88
fi

. ${DIR}/run-config

function create_sandbox_db() {
	if [[ -z "$APP_DB_PASSWORD" ]]
	then
		PARAM_APP_DB_PASSWORD=""
	else
		PARAM_APP_DB_PASSWORD="-p${APP_DB_PASSWORD}"
	fi

	if [ -z "$TEST_DB_PASSWORD" ]
	then
		PARAM_TEST_DB_PASSWORD=""
	else
		PARAM_TEST_DB_PASSWORD="-p${TEST_DB_PASSWORD}"
	fi

	APP_MYSQL_CMD_BASE="mysql -u ${APP_DB_USER} ${PARAM_APP_DB_PASSWORD} ${APP_DB_NAME}"
	APP_MYSQLDUMP_CMD_BASE="mysqldump -u ${APP_DB_USER} -p${APP_DB_PASSWORD} ${APP_DB_NAME}"

	TEST_MYSQL_CMD_BASE_NO_DB="mysql -u ${TEST_DB_USER} ${PARAM_TEST_DB_PASSWORD}"
	TEST_MYSQL_CMD_BASE="${TEST_MYSQL_CMD_BASE_NO_DB} ${TEST_DB_NAME}"

	echo "DROP DATABASE IF EXISTS ${TEST_DB_NAME}" | ${TEST_MYSQL_CMD_BASE_NO_DB}
	echo "CREATE DATABASE ${TEST_DB_NAME}" | ${TEST_MYSQL_CMD_BASE_NO_DB}

	TABLES=$(echo "SHOW TABLES" | ${APP_MYSQL_CMD_BASE} | sed 1d)

	for table in $TABLES
	do
		if [[ $table == lst* ]]
		then
			echo "#${table}"
			${APP_MYSQLDUMP_CMD_BASE} ${table} > ${TMP_SQL_FILE}
			${TEST_MYSQL_CMD_BASE} < ${TMP_SQL_FILE}
		else
			echo "!${table}"
			${APP_MYSQLDUMP_CMD_BASE} ${table} --no-data > ${TMP_SQL_FILE}
			${TEST_MYSQL_CMD_BASE} < ${TMP_SQL_FILE}
			QUERY="SET foreign_key_checks=0;INSERT INTO \`${table}\` (id,ins_dt, ins_process_id) VALUES (-1, NOW(), 'INIT');"
			echo ${QUERY} | ${TEST_MYSQL_CMD_BASE}
		fi

	done
}

function is_changelog_change() {
	# find ${DIR_CHANGELOGS} -type f -printf '%T@ %P\n' | sort -nr > ${TMP_LAST_CHANGELOG_FILE}
	ls -l ${DIR_CHANGELOGS} > ${TMP_LAST_CHANGELOG_FILE}

	LAST_CHANGELOG_FILE=${DIR}/${LAST_CHANGELOG_FILENAME}

	if [ -f ${LAST_CHANGELOG_FILE} ]
	then
		if cmp ${TMP_LAST_CHANGELOG_FILE} ${LAST_CHANGELOG_FILE}
		then
			echo "changelog not change"
			return $FALSE
		else
			echo "changelog changed!!!!!!!!!!!!!!!!!!!!!"
			cp ${TMP_LAST_CHANGELOG_FILE} ${LAST_CHANGELOG_FILE}
			return $TRUE
		fi
	else
		echo "this is first time you run script"
		cp ${TMP_LAST_CHANGELOG_FILE} ${LAST_CHANGELOG_FILE}
		return $TRUE
	fi
}

function run_test() {
	if [ ! -f "$RUNNER_SCRIPT_FILE" ]; then
		echo "Nette Tester is missing. You can install it using Composer:" >&2
		echo "php composer.phar update --dev." >&2
		exit 2
	fi

	# Path to php.ini if passed as argument option
	phpIni=
	while getopts ":c:" opt; do
		case $opt in
		c)	phpIni="$OPTARG"
			;;

		:)	echo "Missing argument for -$OPTARG option" >&2
			exit 2
			;;
		esac
	done



	# Runs tests with script's arguments, add default php.ini if not specified
	# Doubled -c option intentionally
	if [ -n "$phpIni" ]; then
		php -c "$phpIni" "$RUNNER_SCRIPT_FILE" -j 20 "$@"
	else
		php -c "$DIR/php.ini-unix" "$RUNNER_SCRIPT_FILE" -j 20 "$@"
	fi

	error=$?

	# Print *.actual content if tests failed
	if [ "${VERBOSE-false}" != "false" -a $error -ne 0 ]; then
		for i in $(find . -name \*.actual); do echo "--- $i"; cat $i; echo; echo; done
		exit $error
	fi
}

if is_changelog_change
then
	create_sandbox_db
	run_test
else
	run_test
fi

