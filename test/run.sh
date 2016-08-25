#!/bin/bash

CWD=$(dirname $(readlink -f ${BASH_SOURCE[0]}))

PW_REPO="https://github.com/ryancramerdesign/ProcessWire"

# load config
if [ ! -e "${CWD}/config.sh" ]; then
	echo "[ERROR] Missing config! Please, copy test/config.sh.example to test/config.sh and fill the values."
	exit 1
fi

. ${CWD}/config.sh

HASH_LIST="$(git ls-remote --tags "$PW_REPO" | sed -e 's/	/;/')"

WIRESHELL_CMD="${CWD}/../vendor/bin/wireshell"
PHPUNIT_CMD="${CWD}/../vendor/bin/phpunit"

install_pw() {
	wireshell_params="$@"

	echo "create database ${DB_NAME}" | mysql -h "${DB_HOST}" -P ${DB_PORT} -u ${DB_USER} -p"${DB_PASS}" || return 1

	export PW_PATH="${CWD}/../test/.tmp/pw-${final_tag}"

	echo "Installing ProcessWire"
	${WIRESHELL_CMD} new ${wireshell_params} \
		--dbHost ${DB_HOST} --dbPort ${DB_PORT} --dbName ${DB_NAME} --dbUser ${DB_USER} --dbPass "${DB_PASS}" \
		--adminUrl admin --username admin --userpass admin01 --useremail admin@example.com \
		--httpHosts localhost --timezone Europe/Prague \
		"${PW_PATH}" > /dev/null 2>&1

	chmod 777 -R "${PW_PATH}"

	MODULE_DIR="${PW_PATH}/site/modules/ProcessWire-FieldtypePDF"
	mkdir "${MODULE_DIR}"
	cp -r FieldtypePDF "${MODULE_DIR}"
	cp FieldtypePDF.module "${MODULE_DIR}"
	cp InputfieldPDF.module "${MODULE_DIR}"
	cp InputfieldPDF.css "${MODULE_DIR}"
}

uninstall_pw() {
	rm -rf "${CWD}/../test/.tmp"
	echo "drop database if exists ${DB_NAME}" | mysql -h "${DB_HOST}" -P ${DB_PORT} -u ${DB_USER} -p"${DB_PASS}"
}

test_pw() {
	install_params="$@"

	install_pw ${install_params} && ${PHPUNIT_CMD}
	uninstall_pw
}

for tag in ${TEST_TAGS[*]}; do
	for hash_line in ${HASH_LIST}; do
		current_hash=$(echo "$hash_line" | cut -f1 -d";")
		current_tag=$(echo "$hash_line" | cut -f2 -d";" | sed -e 's/refs\/tags\///')
		if [[ ${current_tag} == ${tag}* ]]; then
			final_hash=${current_hash}
			final_tag=${current_tag}
		fi
	done

	echo "Test against PW ${final_tag}"
	test_pw --sha ${final_hash}
done

if [ "${TEST_MASTER}" -eq "1" ]; then
	echo "Test against PW master"
	test_pw
fi
