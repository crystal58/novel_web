include /usr/share/yongche-release/base.mk


NAME=php-pear-YC-Queue
VERSION=$(shell awk '/^Version:/ {print $$2; exit;}' ${NAME}.spec)

all: fetch build copy


fetch:
	cd src && tar cfz ${RPMBUILD_ROOT}/SOURCES/${NAME}.tgz *

build:
	${RPMBUILD_SPEC} ${NAME}.spec ${RPMBUILD_OPTIONS}

copy:
	cp ${RPMBUILD_ROOT}/RPMS/noarch/*${NAME}-${VERSION}*rpm  . 

