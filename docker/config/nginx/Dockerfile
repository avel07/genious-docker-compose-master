FROM alpine:edge

# for shadow package
RUN echo http://dl-2.alpinelinux.org/alpine/edge/community/ >> /etc/apk/repositories

# shadow for usermod
RUN apk add --no-cache nginx-mod-http-brotli shadow gettext

RUN usermod -u 1000 nginx
RUN groupmod -g 1000 nginx

RUN mkdir /etc/nginx/sites-available

COPY docker-entrypoint.sh /
RUN chmod +x /docker-entrypoint.sh
ENTRYPOINT ["/docker-entrypoint.sh"]

# run nginx with configuration reload once in every 6 hours
CMD /bin/sh -c 'while :; do /bin/sleep 6h & wait ${!}; /usr/sbin/nginx -s reload; done & /usr/sbin/nginx -g "daemon off;"'
