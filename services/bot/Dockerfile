FROM debian:latest

MAINTAINER Jacques Bodin-Hullin <j.bodinhullin@monsieurbiz.com> @jacquesbh

# Install some packages
RUN \
    apt-get update && \
    apt-get install -y wget curl runit git && \
    apt-get clean && \
    rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

# Install PHP 7
RUN \
    echo "deb http://packages.dotdeb.org jessie all" > /etc/apt/sources.list.d/dotdeb.list && \
    echo "deb-src http://packages.dotdeb.org jessie all" >> /etc/apt/sources.list.d/dotdeb.list && \
    wget http://www.dotdeb.org/dotdeb.gpg -O- -q | apt-key add - && \
    apt-get update && \
    apt-get install -y php7.0-cli php7.0-curl && \
    apt-get clean && \
    rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* /etc/apt/sources.list.d/*

# Install composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/bin --filename=composer

# Services
COPY bot/run.sh /etc/service/bot/run

RUN echo "youpi"

CMD ["/usr/bin/runsvdir", "-P", "/etc/service"]
