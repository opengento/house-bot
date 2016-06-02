#!/usr/bin/env python
# -*- coding: utf-8 -*-

from fabric.api import local, run, cd, env, task, put, abort
from distutils.util import strtobool

"""
    The purpose of this fabfile is to deploy the House Bot in production.
    @author Jacques Bodin-Hullin <@jacquesbh> <j.bodinhullin@monsieurbiz.com>
"""

# Settings
# ¯¯¯¯¯¯¯¯

env.settings_by_host = {
    # Production
    'monsieurbiz.com': {
        'tags': 'production git docker',
        'host': 'madamebiz@monsieurbiz.com:22',
        'path': '/home/madamebiz/opengento/house-bot'
        }
    }

# Environments
# ¯¯¯¯¯¯¯¯¯¯¯¯

# env.host = 'madamebiz@monsieurbiz.com'

@task
def prod(filters=False):
    """ Init fabric for the production environment """
    env.environment = "production";
    _init_hosts('production', filters)


# Tasks
# ¯¯¯¯¯

@task
def deploy(branch=False,tag=False):
    """ Deploy """
    fetch()
    checkout_branch(branch)
    checkout_tag(tag)
    if not bool(branch) and not bool(tag):
        pull()
    _restart_bot()


@task
def restart():
    """ Restart the bot """
    _restart_bot()


# Methods
# ¯¯¯¯¯¯¯

def fetch():
    """ Fetch the repository """
    if _has_tag('git'):
        with cd(_get_setting('path')):
            run('git fetch')
            run('git fetch --tags')
            run('git remote prune origin')


def checkout_branch(branch=False):
    """ Checkout a branch """
    if _has_tag('git') and branch != False:
        with cd(_get_setting('path')):
            run('git checkout %s' % branch)
            run('git reset --hard origin/%s' % branch)


def checkout_tag(tag=False):
    """ Checkout a tag """
    if _has_tag('git') and tag != False:
        with cd(_get_setting('path')):
            run('git checkout %s' % tag)


# Protected methods
# ¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯

def _restart_bot():
    """ Restart the docker container(s) """
    if _has_tag('docker'):
        with cd(_get_setting('path')):
            run("make prod-down prod-up")


def _init_hosts(tag, filters=False):
    """ Init the hosts according to the environment tag """
    hosts = []
    for host in env.settings_by_host:
        if _has_tag(tag, host):

            canAppend = True;
            if filters != False:
                for filterTag in filters.split(','):
                    if not _has_tag(filterTag, host):
                        canAppend = False;

            if canAppend:
                hosts.append(_get_setting('host', host))

    env.update({
        'hosts': hosts
        })


def _has_tag(tag, host=False):
    """ Returns if the host has the tag """
    tags = _get_setting('tags', host).split(' ')
    return (tag in tags)


def _get_setting(setting=False,host=False,hostTag=False):
    """ Returns the host's settings (or the specified one) """
    if not host:
        if not hostTag:
            host = env.host
        else:
            for hostName in env.settings_by_host:
                if _has_tag(hostTag, hostName):
                    host = hostName
            if not host:
                abort('Impossible to find a host with the tag %s' % hostTag)

    settings = env.settings_by_host[host]
    if not setting:
        return settings
    return settings[setting]

