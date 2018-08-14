%define peardir   /usr/share/pear
%define pear_name YC-Queue

%{?scl: %scl_package php-pear-%{pear_name}}

Name:           php-pear-%{pear_name}
Version:        0.1.1
Release:        2
Summary:        classes for YC-Queue
Vendor:         Yongche Inc.
Packager:       %{_packager}

Group:          Development/Libraries
License:        PHP
URL:            https://git.yongche.org/php/php-pear
Source0:        php-pear-%{pear_name}.tgz

BuildRoot:      %{_tmppath}/%{name}-%{version}-%{release}-root-%(%{__id_u} -n)

BuildArch:      noarch

Requires:       yc-util
BuildRequires:  yongche-release-devel >= 0.3.3-4

Provides:	    php-pear(%{pear_name}) = %{version}


%package -n     %{?scl_prefix}%{name}
Summary:        classes for YCL_Queue

Requires:      %{name} = %{version}
Requires:      %{?scl_prefix}php-pecl-amqp


%description
%{name}

%description -n %{?scl_prefix}%{name}
%{name}

%prep
%setup -q -c %{Source0}

%build

%install
rm -rf %{buildroot}
%{__mkdir_p} %{buildroot}%{peardir}
cp -r YCL %{buildroot}%{peardir}

%clean
rm -rf %{buildroot}

%files
%defattr(-,root,root)
%{peardir}/*

%files -n %{?scl_prefix}%{name}

%changelog

* Tue Jan 17 2017 - guoxiaodong<guoxiaodong@yongche.com> 0.1.1-2
- change default read_timeout for amqp to 0
* Mon Jan 16 2017 - guoxiaodong<guoxiaodong@yongche.com> 0.1.1
- add timeout for amqp
* Wed Dec 7 2016 - guoxiaodong<guoxiaodong@yongche.com> 0.1.0
- init version
