<?php

/**
 * Daemon list.
 *
 * In an ideal world, we would be able to scan the list of scripts in 
 * /etc/rc.d/init.d and generate the service list on the fly.  Unfortunately
 * there are some inconsistencies that make this impossible.
 *
 * A list of services that we care about are below:
 * - keyed on init.d script
 * - array holds:
 *   - the RPM where the daemon lives
 *   - the daemon/process name (what you see with ps)
 *   - whether or not the daemon supports a '/etc/rc.d/init.d/<xyz> reload'
 *   - a short title (uses language templates)
 *   - core daemon (no configuration, but still important)
 *   - configuration URL
 *
 * A few daemons are not really 'running' per se, but are part of the kernel,
 * e.g. the firewall and bandwidth limiter.  Specify 'kernel' for the 
 * process name -- the daemon class will handle these differently.
 *
 * @category   Apps
 * @package    Base
 * @subpackage Libraries
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2006-2011 ClearFoundation
 * @license    http://www.gnu.org/copyleft/lgpl.html GNU Lesser General Public License version 3 or later
 * @link       http://www.clearfoundation.com/docs/developer/apps/base/
 */

$DAEMONS = array(
    'amavisd'       => array('amavisd-new',      'amavisd',       'no',   lang('base_software_amavis'),       'yes', NULL),
    'atalk'         => array('netatalk',         'atalkd',        'no',   lang('base_software_appletalk'),    'no',  NULL),
    'autofs'        => array('autofs',           'automount',     'yes',  lang('base_software_autofs'),       'yes', NULL),
    'bandwidth'     => array('app-bandwidth',    'kernel',        'no',   lang('base_software_bandwidth'),    'no',  'bandwidth.php'),
    'clamd'         => array('clamav-server',    'clamd',         'no',   lang('base_software_clamav'),       'no',  'antivirus.php'),
    'crond'         => array('vixie-cron',       'crond',         'yes',  lang('base_software_cron'),         'yes', NULL),
    'cups'          => array('cups',             'cupsd',         'yes',  lang('base_software_cups'),         'no',  'printing-advanced.php'),
    'cyrus-imapd'   => array('cyrus-imapd',      'cyrus-master',  'no',   lang('base_software_cyrus'),        'no',  'mail-pop-imap.php'),
    'dansguardian-av' => array('dansguardian-av','dansguardian-av','yes', lang('base_software_dansguardian'), 'no', 'proxy-filter.php'),
    'dhcpd'         => array('dhcp',             'dhcpd',         'no',   lang('base_software_dhcp'),         'no',  NULL),
    'dnsmasq'       => array('dnsmasq',          'dnsmasq',       'no',   lang('base_software_dnsmasq'),      'no',  'dhcp.php'),
    'fetchmail'     => array('fetchmail',        'fetchmail',     'no',   lang('base_software_fetchmail'),    'no',  'mail-retrieval.php'),
    'firewall'      => array('app-firewall',     'kernel',        'no',   lang('base_software_firewall'),     'yes', 'firewall.php'),
    'freshclam'     => array('clamav',           'freshclam ',    'no',   lang('base_software_freshclam'),    'no',  'antivirus.php'),
    'httpd'         => array('httpd',            'httpd',         'yes',  lang('base_software_httpd'),        'no',  'web-server.php'),
    'ipsec'         => array('openswan',         'pluto',         'yes',  lang('base_software_ipsec'),        'no',  'ipsec.php'),
    'l7-filter'     => array('l7-filter-userspace', 'l7-filter',  'no',   lang('base_software_l7filter'),     'no',  'protocol-filter.php'),
    'nslcd'         => array('nss-pam-ldapd',    'nslcd',         'no',   lang('base_directory_service'),     'no',  'fixme.php'),
    'slapd'         => array('openldap-servers', 'slapd',         'no',   lang('base_software_ldap'),         'yes', NULL),
    'ldapsync'      => array('kolabd',           'ldapsync',      'no',   lang('base_software_kolab'),        'yes', NULL),
    'mysqld'        => array('mysql-server',     'mysqld',        'no',   lang('base_software_mysql'),        'no',  'mysql.php'),
    'network'       => array('initscripts',      'kernel',        'no',   lang('base_software_network'),      'yes', 'network.php'),
    'nfs'           => array('nfs-utils',        'nfsd',          'no',   lang('base_software_nfs'),          'no',  NULL),
    'nmb'           => array('samba',            'nmbd',          'no',   lang('base_software_nmbd'),         'no',  'samba.php'),
    'ntpd'          => array('ntp',              'ntpd',          'no',   lang('base_software_ntpd'),         'no',  'date.php'),
    'openvpn'       => array('openvpn',          'openvpn',       'no',   lang('base_software_openvpn'),      'no',  'openvpn.php'),
    'pcmcia'        => array('pcmcia-cs',        'cardmgr',       'no',   lang('base_software_pcmcia'),       'yes', NULL),
    'portmap'       => array('portmap',          'portmap',       'no',   lang('base_software_portmap'),      'no',  NULL),
    'postfix'       => array('postfix',          'master',        'yes',  lang('base_software_postfix'),      'no',  'mail-smtp.php'),
    'postgrey'      => array('postgrey',         'postgrey',      'no',   lang('base_software_greylist'),     'no',  'mail-greylisting.php'),
    'pptpd'         => array('pptpd',            'pptpd',         'no',   lang('base_software_pptp'),         'no',  'pptpd.php'),
    'proftpd'       => array('proftpd',          'proftpd',       'yes',  lang('base_software_proftp'),       'no',  'ftp.php'),
    'radiusd'       => array('freeradius2',      'radiusd',       'yes',  lang('base_software_radius'),       'no',  'radius.php'),
    'saslauthd'     => array('cyrus-sasl',       'saslauthd',     'no',   lang('base_software_saslauthd'),     'yes', NULL),
    'smartd'        => array('smartmontools',    'smartd',        'no',   lang('base_software_smartd'),       'yes', NULL),
    'smb'           => array('samba',            'smbd',          'no',   lang('base_software_samba'),        'no',  'samba.php'),
    'snort'         => array('snort',            'snort',         'no',   lang('base_software_snort'),        'no',  'intrusion-detection.php'),
    'snortsam'      => array('snort',            'snortsam',      'no',   lang('base_software_snortsam'),     'no',  'intrusion-prevention.php'),
    'spamassassin'  => array('spamassassin',     'spamd',         'no',   lang('base_software_spamassassin'), 'no',  'mail-antispam.php'),
    'squid'         => array('squid',            'squid',         'yes',  lang('base_software_squid'),        'no',  'proxy.php'),
    'sshd'          => array('openssh',          'sshd',          'yes',  lang('base_software_shell'),        'yes', NULL),
    'suvad'         => array('suva-client',      'suvad',         'no',   'suva',                      'yes', NULL),
    'syslog'        => array('sysklogd',         'syslogd',       'yes',  lang('base_software_syslog'),       'yes', NULL),
    'system-mysqld' => array('system-mysql',     'system-mysqld', 'no',   lang('base_software_system_database'), 'yes',  NULL),
    'syswatch'      => array('app-syswatch',     'syswatch',      'no',   lang('base_software_syswatch'),     'yes', NULL),
    'transmission-daemon' => array('transmission-daemon', 'transmission-daemon', 'no', lang('base_software_transmission'), 'no',  NULL),
    'upnpd'         => array('linuxigd',         'upnpd',         'no',   lang('base_software_upnp'),                   'no',  NULL),
    'vpnwatchd'     => array('app-ipsec',        'vpnwatchd',     'yes',  lang('base_software_vpnwatch'),     'no',  'ipsec.php'),
    'webconfig'     => array('app-webconfig',    'webconfig',     'no',   'webconfig',                 'yes', NULL),
    'winbind'       => array('samba-winbind',    'winbindd',      'no',   lang('base_software_winbind'),      'yes',  NULL),
    'xinetd'        => array('xinetd',           'xinetd',        'yes',  lang('base_software_xinet'),        'no',  NULL),
);

ksort($DAEMONS);
