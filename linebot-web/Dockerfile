FROM php:8.0-fpm

ENV ORACLE_HOME=/opt/oracle

ENV LD_LIBRARY_PATH=$LD_LIBRARY_PATH:$ORACLE_HOME/lib

RUN apt-get update && apt-get install -y libaio1 unzip 

RUN curl -OL https://download.oracle.com/otn_software/linux/instantclient/191000/instantclient-basic-linux.arm64-19.10.0.0.0dbru.zip

RUN curl -OL https://download.oracle.com/otn_software/linux/instantclient/191000/instantclient-sdk-linux.arm64-19.10.0.0.0dbru.zip

RUN unzip instantclient-basic-linux.arm64-19.10.0.0.0dbru.zip && unzip instantclient-sdk-linux.arm64-19.10.0.0.0dbru.zip

RUN mkdir -p $ORACLE_HOME &&  mv instantclient_19_10 $ORACLE_HOME/lib && mkdir -p /opt/oracle/network/admin

COPY ./Wallet_DB202110141010.zip /opt/oracle/network/admin/Wallet_DB202110141010.zip

RUN sh -c 'cd /opt/oracle/network/admin/ && unzip -B Wallet_*.zip && rm -f instantclient-*.zip'

RUN docker-php-ext-configure oci8 --with-oci8=instantclient,/opt/oracle/lib &&  docker-php-ext-install oci8 &&  docker-php-ext-configure pdo_oci --with-pdo-oci=instantclient,/opt/oracle/lib &&  docker-php-ext-install pdo_oci

COPY ./zz-docker.conf /usr/local/etc/php-fpm.d/zz-docker.conf

COPY ./linebot-web /var/www/html

RUN groupadd -g 101 nginx && useradd -u 101 -g 101 nginx
