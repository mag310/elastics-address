FROM registry.is74.ru/docker/web/php:7.2-fpm-r1

RUN git clone git://github.com/mote0230/dbase-pecl-php7.git ~/php7-dbase
RUN    cd ~/php7-dbase/ &&  phpize &&  ./configure &&  make  && make install
RUN    cd ~
RUN    rm -rf ~/php7-dbase

RUN touch /usr/local/etc/php/conf.d/dbase.ini
RUN echo "extension=dbase.so" > /usr/local/etc/php/conf.d/dbase.ini