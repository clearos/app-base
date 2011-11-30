
Name: app-base
Group: ClearOS/Apps
Version: 6.1.0.beta2
Release: 1%{dist}
Summary: General Settings
License: GPLv3
Packager: ClearFoundation
Vendor: ClearFoundation
Source: %{name}-%{version}.tar.gz
Buildarch: noarch
Requires: %{name}-core = %{version}-%{release}
Obsoletes: app-shutdown

%description
Welcome to ClearOS Enterprise 6!

%package core
Summary: General Settings - APIs and install
Group: ClearOS/Libraries
License: LGPLv3
Requires: clearos-base
Requires: clearos-framework
Requires: system-theme
Requires: chkconfig
Requires: coreutils
Requires: file
Requires: initscripts
Requires: passwd
Requires: rpm
Requires: shadow-utils
Requires: sudo
Requires: sysvinit-tools
Requires: syswatch
Requires: util-linux-ng
Requires: webconfig-mod_ssl
Requires: webconfig-php
Requires: webconfig-utils
Obsoletes: app-shutdown-core

%description core
Welcome to ClearOS Enterprise 6!

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
install -D -m 0644 packaging/access_control.conf %{buildroot}/etc/clearos/base.d/access_control.conf
install -D -m 0644 packaging/app-base.cron %{buildroot}/etc/cron.d/app-base
install -D -m 0644 packaging/base %{buildroot}/var/clearos/base/access_control/public
install -D -m 0644 packaging/clearos-beta.repo %{buildroot}/etc/yum.repos.d/clearos-beta.repo
install -D -m 0755 packaging/syncaction %{buildroot}/usr/sbin/syncaction
install -D -m 0755 packaging/webconfig-restart %{buildroot}/usr/sbin/webconfig-restart

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
/usr/clearos/apps/base/deploy
/usr/clearos/apps/base/language
/usr/clearos/apps/base/libraries
/etc/clearos/base.d/access_control.conf
/etc/cron.d/app-base
/var/clearos/base/access_control/public
/etc/yum.repos.d/clearos-beta.repo
/usr/sbin/syncaction
/usr/sbin/webconfig-restart
