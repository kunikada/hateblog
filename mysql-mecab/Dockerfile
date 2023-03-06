FROM mysql/mysql-server:8.0

RUN microdnf update \
  && microdnf remove mysql-community-server-minimal \
  && microdnf module disable mysql \
  && microdnf install -y mysql-community-server mysql-community-devel mecab mecab-ipadic \
  && microdnf clean all \
  && rm -rf /var/cache/yum

RUN echo "!includedir /etc/my.cnf.d" >> /etc/my.cnf
COPY mecab.cnf /etc/my.cnf.d/
COPY sql/ /docker-entrypoint-initdb.d/
