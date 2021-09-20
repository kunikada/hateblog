FROM mysql/mysql-server

RUN microdnf update \
  && microdnf remove mysql-community-server-minimal \
  && microdnf install -y --disablerepo=ol8_appstream --enablerepo=mysql80-server-minimal mysql-community-server mysql-community-devel \
  && microdnf install -y mecab mecab-ipadic \
  && microdnf clean all \
  && rm -rf /var/cache/yum

RUN echo "!includedir /etc/my.cnf.d" >> /etc/my.cnf
COPY mecab.cnf /etc/my.cnf.d/
COPY sql/ /docker-entrypoint-initdb.d/
