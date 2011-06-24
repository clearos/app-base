
Name: app-base
Group: ClearOS/Apps
Version: 5.9.9.2
Release: 3.1%{dist}
Summary: Base system and settings
License: GPLv3
Packager: ClearFoundation
Vendor: ClearFoundation
Source: %{name}-%{version}.tar.gz
Buildarch: noarch
Requires: %{name}-core = %{version}-%{release}

%description
Base system and settings ... blah blah.

%package core
Summary: Base system and settings - APIs and install
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
Requires: webconfig-php
Requires: webconfig-utils

%description core
Base system and settings ... blah blah.

This package provides the core API and libraries.

%prep
%setup -q
%build

%install
mkdir -p -m 755 %{buildroot}/usr/clearos/apps/base
cp -r * %{buildroot}/usr/clearos/apps/base/

install -d -m 0755 %{buildroot}/var/clearos/base
install -d -m 0755 %{buildroot}/var/clearos/base/access_control
install -d -m 0755 %{buildroot}/var/clearos/base/access_control/authenticated
install -d -m 0755 %{buildroot}/var/clearos/base/access_control/custom
install -d -m 0755 %{buildroot}/var/clearos/base/access_control/public
install -D -m 0644 packaging/base %{buildroot}/var/clearos/base/access_control/public
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
%dir /var/clearos/base
%dir /var/clearos/base/access_control
%dir /var/clearos/base/access_control/authenticated
%dir /var/clearos/base/access_control/custom
%dir /var/clearos/base/access_control/public
/usr/clearos/apps/base/deploy
/usr/clearos/apps/base/language
/usr/clearos/apps/base/libraries
/var/clearos/base/access_control/public
/usr/sbin/webconfig-restart
