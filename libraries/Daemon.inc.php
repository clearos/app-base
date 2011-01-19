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
    'amavisd'       => array('amavisd-new',      'amavisd',       'no',   lang('daemon_amavis'),       'yes', NULL),
    'atalk'         => array('netatalk',         'atalkd',        'no',   lang('daemon_appletalk'),    'no',  NULL),
    'autofs'        => array('autofs',           'automount',     'yes',  lang('daemon_autofs'),       'yes', NULL),
    'bacula-dir'    => array('bacula-mysql',     'bacula-dir',    'no',   lang('daemon_bacula_dir'),   'no',  'bacula.php'),
    'bacula-fd'     => array('bacula-mysql',     'bacula-fd',     'no',   lang('daemon_bacula_fd'),    'no',  'bacula.php'),
    'bacula-mysqld' => array('bacula-mysql',     'bacula-mysqld', 'no',   lang('daemon_bacula_mysql'), 'no',  'bacula.php'),
    'bacula-sd'     => array('bacula-mysql',     'bacula-sd',     'no',   lang('daemon_bacula_sd'),    'no',  'bacula.php'),
    'bandwidth'     => array('app-bandwidth',    'kernel',        'no',   lang('daemon_bandwidth'),    'no',  'bandwidth.php'),
    'clamd'         => array('clamav-server',    'clamd',         'no',   lang('daemon_clamav'),       'no',  'antivirus.php'),
    'crond'         => array('vixie-cron',       'crond',         'yes',  lang('daemon_cron'),         'yes', NULL),
    'cups'          => array('cups',             'cupsd',         'yes',  lang('daemon_cups'),         'no',  'printing-advanced.php'),
    'cyrus-imapd'   => array('cyrus-imapd',      'cyrus-master',  'no',   lang('daemon_cyrus'),        'no',  'mail-pop-imap.php'),
    'dansguardian-av' => array('dansguardian-av','dansguardian-av','yes', lang('daemon_dansguardian'), 'no', 'proxy-filter.php'),
    'dhcpd'         => array('dhcp',             'dhcpd',         'no',   lang('daemon_dhcp'),         'no',  NULL),
    'dnsmasq'       => array('dnsmasq',          'dnsmasq',       'no',   lang('daemon_dnsmasq'),      'no',  'dhcp.php'),
    'fetchmail'     => array('fetchmail',        'fetchmail',     'no',   lang('daemon_fetchmail'),    'no',  'mail-retrieval.php'),
    'firewall'      => array('app-firewall',     'kernel',        'no',   lang('daemon_firewall'),     'yes', 'firewall.php'),
    'freshclam'     => array('clamav',           'freshclam ',    'no',   lang('daemon_freshclam'),    'no',  'antivirus.php'),
    'httpd'         => array('httpd',            'httpd',         'yes',  lang('daemon_httpd'),        'no',  'web-server.php'),
    'ipsec'         => array('openswan',         'pluto',         'yes',  lang('daemon_ipsec'),        'no',  'ipsec.php'),
    'l7-filter'     => array('l7-filter-userspace', 'l7-filter',  'no',   lang('daemon_l7filter'),     'no',  'protocol-filter.php'),
    'ldap'          => array('openldap-servers', 'slapd',         'no',   lang('daemon_ldap'),         'yes', NULL),
    'ldapsync'      => array('kolabd',           'ldapsync',      'no',   lang('daemon_kolab'),        'yes', NULL),
    'mysqld'        => array('mysql-server',     'mysqld',        'no',   lang('daemon_mysql'),        'no',  'mysql.php'),
    'network'       => array('initscripts',      'kernel',        'no',   lang('daemon_network'),      'yes', 'network.php'),
    'nfs'             => array('nfs-utils',        'nfsd',          'no',   lang('daemon_nfs'),          'no',  NULL),
    'nmb'           => array('samba',            'nmbd',          'no',   lang('daemon_nmbd'),         'no',  'samba.php'),
    'ntpd'          => array('ntp',              'ntpd',          'no',   lang('daemon_ntpd'),         'no',  'date.php'),
    'openvpn'       => array('openvpn',          'openvpn',       'no',   lang('daemon_openvpn'),      'no',  'openvpn.php'),
    'pcmcia'        => array('pcmcia-cs',        'cardmgr',       'no',   lang('daemon_pcmcia'),       'yes', NULL),
    'portmap'       => array('portmap',          'portmap',       'no',   lang('daemon_portmap'),      'no',  NULL),
    'postfix'       => array('postfix',          'master',        'yes',  lang('daemon_postfix'),      'no',  'mail-smtp.php'),
    'postgrey'      => array('postgrey',         'postgrey',      'no',   lang('daemon_greylist'),     'no',  'mail-greylisting.php'),
    'pptpd'         => array('pptpd',            'pptpd',         'no',   lang('daemon_pptp'),         'no',  'pptpd.php'),
    'proftpd'       => array('proftpd',          'proftpd',       'yes',  lang('daemon_proftp'),       'no',  'ftp.php'),
    'radiusd'       => array('freeradius2',      'radiusd',       'yes',  lang('daemon_radius'),       'no',  'radius.php'),
    'saslauthd'     => array('cyrus-sasl',       'saslauthd',     'no',   lang('daemon_saslauthd'),     'yes', NULL),
    'smartd'        => array('smartmontools',    'smartd',        'no',   lang('daemon_smartd'),       'yes', NULL),
    'smb'           => array('samba',            'smbd',          'no',   lang('daemon_samba'),        'no',  'samba.php'),
    'snort'         => array('snort',            'snort',         'no',   lang('daemon_snort'),        'no',  'intrusion-detection.php'),
    'snortsam'      => array('snort',            'snortsam',      'no',   lang('daemon_snortsam'),     'no',  'intrusion-prevention.php'),
    'spamassassin'  => array('spamassassin',     'spamd',         'no',   lang('daemon_spamassassin'), 'no',  'mail-antispam.php'),
    'squid'         => array('squid',            'squid',         'yes',  lang('daemon_squid'),        'no',  'proxy.php'),
    'sshd'          => array('openssh',          'sshd',          'yes',  lang('daemon_shell'),        'yes', NULL),
    'suvad'         => array('suva-client',      'suvad',         'no',   'suva',                      'yes', NULL),
    'syslog'        => array('sysklogd',         'syslogd',       'yes',  lang('daemon_syslog'),       'yes', NULL),
    'system-mysqld' => array('system-mysql',     'system-mysqld', 'no',   lang('daemon_system_database'), 'yes',  NULL),
    'syswatch'      => array('app-syswatch',     'syswatch',      'no',   lang('daemon_syswatch'),     'yes', NULL),
    'transmission-daemon' => array('transmission-daemon', 'transmission-daemon', 'no', lang('daemon_transmission'), 'no',  NULL),
    'upnpd'         => array('linuxigd',         'upnpd',         'no',   lang('daemon_upnp'),                   'no',  NULL),
    'vpnwatchd'     => array('app-ipsec',        'vpnwatchd',     'yes',  lang('daemon_vpnwatch'),     'no',  'ipsec.php'),
    'webconfig'     => array('app-webconfig',    'webconfig',     'no',   'webconfig',                 'yes', NULL),
    'winbind'       => array('samba-winbind',    'winbindd',      'no',   lang('daemon_winbind'),      'yes',  NULL),
    'xinetd'        => array('xinetd',           'xinetd',        'yes',  lang('daemon_xinet'),        'no',  NULL),
);

ksort($DAEMONS);
