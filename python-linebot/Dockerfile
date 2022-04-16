FROM python:3.7

RUN groupdel ssh && groupadd -g 101 nginx && useradd -u 101 -g 101 nginx

COPY ./python-linebot /home/linebot

RUN chown -R 101:101 /home/linebot

RUN mkdir -p /home/socket && chown -R 101:101 /home/socket

RUN mkdir -p /home/nginx && chown -R 101:101 /home/nginx

ENV ORACLE_HOME=/opt/oracle

ENV LD_LIBRARY_PATH=$LD_LIBRARY_PATH:$ORACLE_HOME/lib

RUN apt-get update && apt-get install -y libaio1 &&  wget -q https://download.oracle.com/otn_software/linux/instantclient/191000/instantclient-basic-linux.arm64-19.10.0.0.0dbru.zip && unzip instantclient-*.zip && mkdir -p $ORACLE_HOME && mv instantclient_19_10 $ORACLE_HOME/lib &&  mkdir -p /opt/oracle/network/admin

COPY ./Wallet_DB202110141010.zip /opt/oracle/network/admin/Wallet_DB202110141010.zip

RUN sh -c 'cd /opt/oracle/network/admin/ && unzip -B Wallet_*.zip && rm -f instantclient-*.zip'

USER nginx

WORKDIR /home/linebot

ENV PATH="/home/nginx/.local/bin:${PATH}"

RUN pip install --user -r requirements.txt

ENV TZ Asia/Tokyo

CMD uwsgi uwsgi.ini
