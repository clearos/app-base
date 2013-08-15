
Name: app-base
Epoch: 1
Version: 1.4.70
Release: 1%{dist}
Summary: General Settings
License: GPLv3
Group: ClearOS/Apps
Source: %{name}-%{version}.tar.gz
Buildarch: noarch
Requires: %{name}-core = 1:%{version}-%{release}

%description
The Base app provides core system libraries and tools.

%package core
Summary: General Settings - Core
License: LGPLv3
Group: ClearOS/Libraries
Requires: clearos-base
Requires: clearos-framework >= 6.4.27
Requires: clearos-release
Requires: csplugin-filewatch
Requires: theme-default >= 6.4.25
Requires: chkconfig
Requires: coreutils
Requires: file
Requires: initscripts
Requires: passwd
Requires: pciutils
Requires: rpm
Requires: shadow-utils
Requires: sudo
Requires: sysvinit-tools
Requires: syswatch >= 6.5.0
Requires: tmpwatch
Requires: util-linux-ng
Requires: usbutils
Requires: webconfig-mod_ssl
Requires: webconfig-php
Requires: webconfig-php-ldap
Requires: webconfig-php-process
Requires: webconfig-php-xml
Requires: webconfig-utils
Requires: webconfig-zend-guard-loader
Requires: yum-utils

%description core
The Base app provides core system libraries and tools.

This package provides the core API and libraries.

%prep
%setup -q
%build

%install
mkdir -p -m 755 %{buildroot}/usr/clearos/apps/base
cp -r * %{buildroot}/usr/clearos/apps/base/

install -d -m 0755 %{buildroot}/etc/clearos/base.d
install -d -m 0755 %{buildroot}/var/clearos/base
install -d -m 0755 %{buildroot}/var/clearos/base/access_control
install -d -m 0755 %{buildroot}/var/clearos/base/access_control/authenticated
install -d -m 0755 %{buildroot}/var/clearos/base/access_control/custom
install -d -m 0755 %{buildroot}/var/clearos/base/access_control/public
install -d -m 0755 %{buildroot}/var/clearos/base/daemon
install -d -m 0755 %{buildroot}/var/clearos/base/translations
install -D -m 0644 packaging/RPM-GPG-KEY-EPEL-6 %{buildroot}/etc/pki/rpm-gpg/CLEAROS-RPM-GPG-KEY-EPEL-6
install -D -m 0644 packaging/access_control.conf %{buildroot}/etc/clearos/base.d/access_control.conf
install -D -m 0644 packaging/app-base.cron %{buildroot}/etc/cron.d/app-base
install -D -m 0644 packaging/base %{buildroot}/var/clearos/base/access_control/public
install -D -m 0644 packaging/base.acl %{buildroot}/var/clearos/base/access_control/authenticated/base
install -D -m 0644 packaging/clearos-developer.repo %{buildroot}/etc/yum.repos.d/clearos-developer.repo
install -D -m 0644 packaging/clearos-epel.repo %{buildroot}/etc/yum.repos.d/clearos-epel.repo
install -D -m 0644 packaging/filewatch-base-clearsync.conf %{buildroot}/etc/clearsync.d/filewatch-base-clearsync.conf
install -D -m 0644 packaging/filewatch-base-install.conf %{buildroot}/etc/clearsync.d/filewatch-base-install.conf
install -D -m 0644 packaging/filewatch-base-webconfig.conf %{buildroot}/etc/clearsync.d/filewatch-base-webconfig.conf
install -D -m 0755 packaging/syncaction %{buildroot}/usr/sbin/syncaction
install -D -m 0755 packaging/wc-yum %{buildroot}/usr/sbin/wc-yum
install -D -m 0755 packaging/webconfig-restart %{buildroot}/usr/sbin/webconfig-restart
install -D -m 0755 packaging/webconfig-service %{buildroot}/usr/sbin/webconfig-service
install -D -m 0644 packaging/wizard.conf %{buildroot}/etc/clearos/base.d/wizard.conf
install -D -m 0755 packaging/yum-install %{buildroot}/usr/sbin/yum-install

%post
logger -p local6.notice -t installer 'app-base - installing'

%post core
logger -p local6.notice -t installer 'app-base-core - installing'

if [ $1 -eq 1 ]; then
    [ -x /usr/clearos/apps/base/deploy/install ] && /usr/clearos/apps/base/deploy/install
fi

[ -x /usr/clearos/apps/base/deploy/upgrade ] && /usr/clearos/apps/base/deploy/upgrade

exit 0

%preun
if [ $1 -eq 0 ]; then
    logger -p local6.notice -t installer 'app-base - uninstalling'
fi

%preun core
if [ $1 -eq 0 ]; then
    logger -p local6.notice -t installer 'app-base-core - uninstalling'
    [ -x /usr/clearos/apps/base/deploy/uninstall ] && /usr/clearos/apps/base/deploy/uninstall
fi

exit 0

%files
%defattr(-,root,root)
/usr/clearos/apps/base/controllers
/usr/clearos/apps/base/htdocs
/usr/clearos/apps/base/views

%files core
%defattr(-,root,root)
%exclude /usr/clearos/apps/base/packaging
%exclude /usr/clearos/apps/base/tests
%dir /usr/clearos/apps/base
%dir /etc/clearos/base.d
%dir /var/clearos/base
%dir /var/clearos/base/access_control
%dir /var/clearos/base/access_control/authenticated
%dir /var/clearos/base/access_control/custom
%dir /var/clearos/base/access_control/public
%dir /var/clearos/base/daemon
%dir /var/clearos/base/translations
/usr/clearos/apps/base/deploy
/usr/clearos/apps/base/language
/usr/clearos/apps/base/libraries
/etc/pki/rpm-gpg/CLEAROS-RPM-GPG-KEY-EPEL-6
/etc/clearos/base.d/access_control.conf
/etc/cron.d/app-base
/var/clearos/base/access_control/public
/var/clearos/base/access_control/authenticated/base
/etc/yum.repos.d/clearos-developer.repo
/etc/yum.repos.d/clearos-epel.repo
/etc/clearsync.d/filewatch-base-clearsync.conf
/etc/clearsync.d/filewatch-base-install.conf
/etc/clearsync.d/filewatch-base-webconfig.conf
/usr/sbin/syncaction
/usr/sbin/wc-yum
/usr/sbin/webconfig-restart
/usr/sbin/webconfig-service
%config(noreplace) /etc/clearos/base.d/wizard.conf
/usr/sbin/yum-install
