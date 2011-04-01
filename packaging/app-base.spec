
Name: app-base
Group: ClearOS/Apps
Version: 6.0
Release: 0.5
Summary: Base system and settings
License: GPLv3
Packager: ClearFoundation
Vendor: ClearFoundation
Source: %{name}-%{version}.tar.gz
Requires: %{name}-core = %{version}-%{release}
Buildarch: noarch

%description
Base system and settings ... blah blah.

%package core
Summary: Core libraries and install for app-base
Group: ClearOS/Libraries
License: LGPLv3
Requires: clearos-base
Requires: clearos-framework
Requires: clearos-theme
Requires: chkconfig
Requires: coreutils
Requires: file
Requires: initscripts
Requires: passwd
Requires: rpm
Requires: shadow-utils
Requires: sudo
Requires: sysvinit-tools
Requires: webconfig-php
Requires: webconfig-utils

%description core
Core API and install for app-base

%prep
%setup -q
%build

%install
mkdir -p -m 755 %{buildroot}/usr/clearos/apps/base
cp -r * %{buildroot}/usr/clearos/apps/base/


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
/usr/clearos/apps/base/config
/usr/clearos/apps/base/deploy
/usr/clearos/apps/base/language
/usr/clearos/apps/base/libraries
