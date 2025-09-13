FROM mysql:8.4.3
ENV TZ=Asia/Tokyo
COPY my.cnf /etc/mysql/conf.d/z-overrides.cnf
RUN chmod 0444 /etc/mysql/conf.d/z-overrides.cnf
