"""ClearCenter Marketplace Repository Plug-in

This is a YUM plug-in which dynamically adds marketplace repositories
"""
import os
import re
import httplib
import urllib
import json
import shutil

from urlparse import urlparse
from yum.plugins import PluginYumExit, TYPE_CORE
from yum.yumRepo import YumRepository as Repository
from xml.etree.ElementTree import parse

requires_api_version = '2.3'
plugin_type = (TYPE_CORE,)

class wcRepo:
    def __init__(self, arch):

        self.url = sdn_url
        self.request = sdn_request
        self.method = sdn_method
        self.basearch = arch

        if os.getenv('SDN_URL') != None:
            self.url = os.getenv('SDN_URL')
        if os.getenv('SDN_REQUEST') != None:
            self.request = os.getenv('SDN_REQUEST')
        if os.getenv('SDN_METHOD') != None:
            self.method = os.getenv('SDN_METHOD')

        self.organization_vendor = { 'clearcenter.com': 'clear' }

    def fetch(self):
        if os.path.exists('/var/clearos/registration/registered') == False:
            return []

        osvendor = None
        fh = open('/etc/product', 'r')
        lines = fh.readlines()
        fh.close()
        for line in lines:
            kv = self.parse_kv_line(line)
            if not kv.has_key('vendor'):
                continue
            osvendor = kv['vendor']
            break
        if osvendor == None:
            raise Exception('OS vendor not found.')

        hostkey = None
        suva_conf = parse(urllib.urlopen('file:///etc/suvad.conf')).getroot()
        for org in suva_conf.findall('organization'):
            if not self.organization_vendor.has_key(org.attrib.get('name')):
                continue
            if self.organization_vendor[org.attrib.get('name')] != osvendor:
                continue
            hostkey = org.findtext('hostkey')
            break

        if hostkey == None or hostkey == '00000000000000000000000000000000':
            raise Exception('system hostkey not found.')

        osname=None
        osversion=None
        fh = open('/etc/product', 'r')
        lines = fh.readlines()
        fh.close()
        for line in lines:
            kv = self.parse_kv_line(line)
            if not kv.has_key('name'):
                continue
            osname = kv['name']
            break
        if osname == None:
            raise Exception('OS name not found.')
        for line in lines:
            kv = self.parse_kv_line(line)
            if not kv.has_key('version'):
                continue
            osversion = kv['version']
            break
        if osversion == None:
            raise Exception('OS version not found.')

        params = {
            'method': self.method, 'hostkey': hostkey,
            'vendor': osvendor, 'osname': osname, 'osversion': osversion }
        request = "%s?%s" %(self.request, urllib.urlencode(params))

        hc = httplib.HTTPSConnection(self.url)
        hc.request("GET", request)
        hr = hc.getresponse()
        if hr.status != 200:
            raise Exception('unable to retrieve repository data.')

        buffer = hr.read()
        response = json.loads(buffer)
        if not response.has_key('code') or not response.has_key('repos'):
            raise Exception('malformed repository data response.')

        if response['code'] != 0:
            raise Exception('malformed repository data.')

        repos = []
        baseurl = False
        for r in response['repos']:
            repo = Repository(r['name'])
            urls = []
            for u in r['baseurl']:
                if len(u['username']) == 0:
                    url = u['url']
                else:
                    url = urlparse(u['url'])
                    port = 80
                    if url.port != None:
                        port = url.port
                    url = "%s://%s:%s@%s:%s%s" %(
                        url.scheme, u['username'], u['password'],
                        url.netloc, port, url.path)
                urls.append(str(url) + '/' + self.basearch)

            repo.setAttribute('baseurl', urls)
            repo.setAttribute('description', r['description'])
            repo.setAttribute('enabled', r['enabled'])
            repo.setAttribute('gpgcheck', r['gpgcheck'])
            repo.setAttribute('name', r['name'])
            repo.enable()
            repos.append(repo)
        return repos

    def parse_kv_line(self, line):
        kv = {}
        rx = re.compile(r'\s*(\w+)\s*=\s*(.*),?')
        for k,v in rx.findall(line):
            if v[-1] == '"':
                v = v[1:-1]
            if '=' in v:
                kv[k] = self.parse_kv_line(self, v)
            else:
                kv[k] = v.rstrip()
        return kv

def init_hook(conduit):
    global sdn_url, sdn_request, sdn_method

    sdn_url = conduit.confString(
        'main', 'sdn_url', default='secure.clearcenter.com')
    sdn_request = conduit.confString(
        'main', 'sdn_request', default='/ws/1.0/marketplace/')
    sdn_method = conduit.confString(
        'main', 'sdn_method', default='get_repo_list')

def prereposetup_hook(conduit):
    global wc_repos

    conduit.info(2, 'ClearCenter Marketplace: fetching repositories...')
    wc_repo = wcRepo(conduit._base.arch.basearch)
    
    try:
        wc_repos = wc_repo.fetch()
        for r in wc_repos:
            conduit._base.repos.add(r)
    except Exception, msg:
        conduit.info(2, 'ClearCenter Marketplace: %s' %msg)

def close_hook(conduit):
        for r in wc_repos:
            shutil.rmtree(str(r), True)

# vi: expandtab shiftwidth=4 softtabstop=4 tabstop=4
