FROM nginx:stable-alpine

ADD ./.docker/config/nginx/nginx.conf /etc/nginx/nginx.conf
ADD ./.docker/config/nginx/default.conf /etc/nginx/conf.d/default.conf

RUN mkdir -p /var/www

RUN addgroup -g 1000 appuser && adduser -G appuser -g appuser -s /bin/sh -D appuser

RUN chown appuser:appuser /var/www