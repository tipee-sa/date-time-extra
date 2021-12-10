FROM composer:latest as composer

FROM php:8-cli-alpine as php

COPY --from=composer /usr/bin/composer /usr/bin/composer

WORKDIR /app
